<?php
require_once __DIR__ . '/../models/Movie.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/Promotion.php';
require_once __DIR__ . '/../models/SeatPrice.php';

class MovieController {
    private Movie $movieModel;
    private Order $orderModel;
    private Ticket $ticketModel;
    private Promotion $promotionModel;
    private SeatPrice $seatPriceModel;
    private string $defaultPoster = 'assets/images/default-poster.svg';
    private string $defaultBanner = 'assets/images/default-banner.svg';

    public function __construct() {
        $this->movieModel = new Movie();
        $this->orderModel = new Order();
        $this->ticketModel = new Ticket();
        $this->promotionModel = new Promotion();
        $this->seatPriceModel = new SeatPrice();
    }

    private function renderAdmin(string $viewPath, array $data = []): void {
        extract($data);
        ob_start();
        include __DIR__ . "/../views/admin/movies/{$viewPath}.php";
        $content = ob_get_clean();
        include __DIR__ . '/../views/layouts/admin_layout.php';
    }

    private function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }

    private function uploadImage(string $inputName, string $prefix, ?string $fallbackPath = null): ?string {
        return upload_file($_FILES[$inputName] ?? [], 'assets/uploads/movies', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], $prefix) ?: $fallbackPath;
    }

    private function validatePromotionForOrder(array $promo, array $order, array $tickets): array {
        $totalTickets = count($tickets);
        $totalAmount = $order['total_amount'];
        $seatTypes = array_unique(array_map(fn($ticket) => $ticket['seat_type'] ?? 'standard', $tickets));

        $minTickets = (int) ($promo['min_tickets'] ?? 0);
        $minAmount = (float) ($promo['min_amount'] ?? $promo['min_order_amount'] ?? 0);

        if ($minTickets > 0 && $totalTickets < $minTickets) {
            return ['applicable' => false, 'reason' => 'Cần ít nhất ' . $minTickets . ' vé để áp dụng mã này.'];
        }
        if ($minAmount > 0 && $totalAmount < $minAmount) {
            return ['applicable' => false, 'reason' => 'Đơn hàng phải từ ' . number_format($minAmount, 0, ',', '.') . '₫ trở lên.'];
        }

        if (!empty($promo['applicable_seat_types'])) {
            $allowedTypes = json_decode($promo['applicable_seat_types'], true);
            if (is_array($allowedTypes) && $allowedTypes) {
                foreach ($seatTypes as $type) {
                    if (!in_array($type, $allowedTypes, true)) {
                        return ['applicable' => false, 'reason' => 'Mã này chỉ áp dụng cho ghế: ' . implode(', ', $allowedTypes) . '.'];
                    }
                }
            }
        }

        return ['applicable' => true, 'reason' => ''];
    }

    public function home(): void {
        $searchQuery = trim($_GET['q'] ?? '');
        if ($searchQuery !== '') {
            $searchQuery = mb_strtolower($searchQuery, 'UTF-8');
            $searchResults = $this->movieModel->searchMovies($searchQuery);
            $movies = array_values(array_filter($searchResults, fn($movie) => $movie['status'] === 'showing'));
            $comingSoon = array_values(array_filter($searchResults, fn($movie) => $movie['status'] === 'coming_soon'));
        } else {
            $movies = $this->movieModel->getShowingMovies();
            $comingSoon = $this->movieModel->getComingSoonMovies();
        }

        $activePromotions = $this->promotionModel->getActivePromotions();
        $allRooms = $this->movieModel->getAllRooms();
        $theaters = [];
        foreach ($allRooms as $room) {
            $roomName = trim($room['room_name']);
            $cinema = 'Rạp khác';
            $hall = $roomName;
            if (strpos($roomName, ' - ') !== false) {
                [$cinema, $hall] = explode(' - ', $roomName, 2);
            }
            $cinema = trim($cinema);
            $hall = trim($hall);
            if (!isset($theaters[$cinema])) {
                $theaters[$cinema] = [];
            }
            $theaters[$cinema][] = ['hall' => $hall, 'capacity' => (int) $room['capacity']];
        }

        include __DIR__ . '/../views/movies/home.php';
    }

    public function detail(): void {
        $movie_id = (int) ($_GET['id'] ?? 0);
        $movie = $this->movieModel->getMovieById($movie_id);
        if (!$movie) {
            header('Location: index.php');
            exit;
        }
        $showtimes = $this->movieModel->getShowtimesByMovie($movie_id);
        include __DIR__ . '/../views/movies/detail.php';
    }

    public function selectShowtime(): void {
        $movie_id = (int) ($_GET['id'] ?? 0);
        $movie = $this->movieModel->getMovieById($movie_id);
        if (!$movie) {
            header('Location: index.php');
            exit;
        }
        $showtimes = $this->movieModel->getShowtimesByMovie($movie_id);
        include __DIR__ . '/../views/booking/select-showtime.php';
    }

    public function book(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: web.php?action=login');
            exit;
        }

        $showtime_id = (int) ($_GET['showtime_id'] ?? 0);
        $showtime = $this->movieModel->getShowtimeById($showtime_id);
        if (!$showtime) {
            header('Location: index.php');
            exit;
        }

        $movie = $this->movieModel->getMovieById((int) $showtime['movie_id']);
        $seatMap = $this->movieModel->getSeatsForShowtime($showtime_id);
        $seatPricesInfo = $this->seatPriceModel->getAll();
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $selectedSeats = array_map('intval', $_POST['seats'] ?? []);
            $promoCode = trim($_POST['promo_code'] ?? '');
            if (empty($selectedSeats)) {
                $errors[] = 'Vui lòng chọn ít nhất một ghế.';
            }

            $offer = null;
            $discountAmount = 0.00;
            $promotion_id = null;
            $totalAmount = 0;
            $seatPrices = [];

            if (empty($errors)) {
                foreach ($seatMap as $seat) {
                    if (in_array((int) $seat['seat_id'], $selectedSeats, true)) {
                        $seatType = $seat['seat_type'] ?? 'standard';
                        $seatPriceInfo = $this->seatPriceModel->getByType($seatType);
                        $multiplier = $seatPriceInfo ? (float) $seatPriceInfo['price_multiplier'] : 1.00;
                        $seatPrice = (float) $showtime['base_price'] * $multiplier;
                        $totalAmount += $seatPrice;
                        $seatPrices[$seat['seat_id']] = $seatPrice;
                    }
                }
            }

            $finalAmount = $totalAmount;
            if ($promoCode !== '') {
                $offer = $this->promotionModel->getByCode($promoCode);
                if (!$offer) {
                    $errors[] = 'Mã khuyến mãi không hợp lệ hoặc đã hết hạn.';
                } else {
                    $promotion_id = $offer['promotion_id'];
                    if (($offer['discount_type'] ?? '') === 'percent') {
                        $discountAmount = round($totalAmount * ((float) $offer['discount_value'] / 100), 2);
                        if (!empty($offer['max_discount'])) {
                            $discountAmount = min($discountAmount, (float) $offer['max_discount']);
                        }
                    } else {
                        $discountAmount = min((float) $offer['discount_value'], $totalAmount);
                    }
                    $finalAmount = max(0, $totalAmount - $discountAmount);
                }
            }

            if (empty($errors)) {
                $orderCode = 'ORD' . time() . rand(100, 999);
                $order_id = $this->orderModel->create((int) $_SESSION['user_id'], $promotion_id, $orderCode, $totalAmount, $discountAmount, $finalAmount);
                if ($order_id) {
                    if ($this->ticketModel->reserveTicketsWithPrice($order_id, $showtime_id, $seatPrices)) {
                        header('Location: web.php?action=checkout&order_id=' . $order_id);
                        exit;
                    }
                    $errors[] = 'Không thể tạo vé. Vui lòng thử lại.';
                } else {
                    $errors[] = 'Không thể tạo đơn đặt vé. Vui lòng thử lại.';
                }
            }
        }

        include __DIR__ . '/../views/booking/book.php';
    }

    public function checkout(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: web.php?action=login');
            exit;
        }

        $order_id = (int) ($_GET['order_id'] ?? 0);
        $order = $this->orderModel->getById($order_id);
        $errors = [];
        $success = '';
        $promoCode = $order['promo_code'] ?? '';
        $activePromotions = $this->promotionModel->getActivePromotions();

        if (!$order || (int) $order['user_id'] !== (int) $_SESSION['user_id']) {
            header('Location: web.php?action=booking-history');
            exit;
        }

        $tickets = $this->ticketModel->getTicketsByOrder($order_id);
        $promoApplicability = [];
        foreach ($activePromotions as $promo) {
            $promoApplicability[$promo['promo_code']] = $this->validatePromotionForOrder($promo, $order, $tickets);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['apply_promo']) || isset($_POST['apply_promo_code'])) {
                $promoCode = trim($_POST['apply_promo_code'] ?? ($_POST['promo_code'] ?? ''));
                if ($promoCode === '') {
                    $errors[] = 'Vui lòng nhập mã giảm giá.';
                } else {
                    $offer = $this->promotionModel->getByCode($promoCode);
                    if (!$offer) {
                        $errors[] = 'Mã khuyến mãi không hợp lệ hoặc đã hết hạn.';
                    } else {
                        $validation = $this->validatePromotionForOrder($offer, $order, $tickets);
                        if (!$validation['applicable']) {
                            $errors[] = $validation['reason'];
                        } else {
                            $discountAmount = 0.00;
                            $totalAmount = $order['total_amount'];
                            if (($offer['discount_type'] ?? '') === 'percent') {
                                $discountAmount = round($totalAmount * ((float) $offer['discount_value'] / 100), 2);
                                if (!empty($offer['max_discount'])) {
                                    $discountAmount = min($discountAmount, (float) $offer['max_discount']);
                                }
                            } else {
                                $discountAmount = min((float) $offer['discount_value'], $totalAmount);
                            }
                            $finalAmount = max(0, $totalAmount - $discountAmount);
                            if ($this->orderModel->updatePromotion($order_id, $offer['promotion_id'], $discountAmount, $finalAmount)) {
                                $success = 'Áp dụng mã giảm giá thành công.';
                                $order = $this->orderModel->getById($order_id);
                            } else {
                                $errors[] = 'Không thể áp dụng mã giảm giá. Vui lòng thử lại.';
                            }
                        }
                    }
                }
            }

            if (isset($_POST['confirm_payment'])) {
                if ($this->orderModel->updateStatus($order_id, 'paid')) {
                    header('Location: web.php?action=booking-success&order_id=' . $order_id);
                    exit;
                }
                $errors[] = 'Không thể cập nhật đơn hàng. Vui lòng thử lại.';
            }
        }

        include __DIR__ . '/../views/booking/checkout.php';
    }

    public function success(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: web.php?action=login');
            exit;
        }
        $order_id = (int) ($_GET['order_id'] ?? 0);
        $order = $this->orderModel->getById($order_id);
        if (!$order || (int) $order['user_id'] !== (int) $_SESSION['user_id']) {
            header('Location: web.php?action=history');
            exit;
        }
        $tickets = $this->ticketModel->getTicketsByOrder($order_id);
        include __DIR__ . '/../views/booking/success.php';
    }

    public function theaters(): void {
        $allRooms = $this->movieModel->getAllRooms();
        $theaters = [];
        foreach ($allRooms as $room) {
            $roomName = trim($room['room_name']);
            $cinema = 'Rạp khác';
            $hall = $roomName;
            if (strpos($roomName, ' - ') !== false) {
                [$cinema, $hall] = explode(' - ', $roomName, 2);
            }
            $cinema = trim($cinema);
            $hall = trim($hall);
            if (!isset($theaters[$cinema])) {
                $theaters[$cinema] = ['name' => $cinema, 'address' => '', 'halls' => []];
            }
            $theaters[$cinema]['halls'][] = ['hall' => $hall, 'capacity' => (int) $room['capacity']];
        }
        include __DIR__ . '/../views/movies/theaters.php';
    }

    public function promotions(): void {
        $activePromotions = $this->promotionModel->getActivePromotions();
        include __DIR__ . '/../views/movies/promotions.php';
    }

    public function index(): void {
        $filters = [
            'keyword' => trim($_GET['keyword'] ?? ''),
            'status' => $_GET['status'] ?? '',
            'genre' => trim($_GET['genre'] ?? ''),
        ];
        $this->renderAdmin('index', [
            'movies' => $this->movieModel->getAll($filters),
            'stats' => $this->movieModel->getStats(),
            'filters' => $filters,
            'genreOptions' => $this->movieModel->getGenreOptions(),
            'activeMenu' => 'movies',
            'breadcrumb' => 'Quản lý phim',
            'pageTitle' => 'Danh sách phim',
        ]);
    }

    public function create(): void {
        $this->renderAdmin('create', [
            'genreOptions' => $this->movieModel->getGenreOptions(),
            'activeMenu' => 'movies',
            'breadcrumb' => 'Thêm phim mới',
            'pageTitle' => 'Thêm phim mới',
        ]);
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?action=movies');
        }
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'director' => trim($_POST['director'] ?? ''),
            'cast' => trim($_POST['cast'] ?? ''),
            'genre' => trim($_POST['genre'] ?? ''),
            'duration' => (int) ($_POST['duration'] ?? 0),
            'release_date' => $_POST['release_date'] ?? null,
            'trailer_url' => trim($_POST['trailer_url'] ?? ''),
            'status' => isset($_POST['status']) ? $_POST['status'] : 1,
            'poster' => $this->uploadImage('poster', 'poster', $this->defaultPoster),
            'banner' => $this->uploadImage('banner', 'banner', $this->defaultBanner),
        ];
        if ($data['title'] === '' || $data['duration'] <= 0 || empty($data['release_date'])) {
            set_flash('danger', 'Dữ liệu phim chưa hợp lệ.');
            $this->redirect('?action=create_movie');
        }
        if ($this->movieModel->create($data)) {
            set_flash('success', 'Thêm phim thành công.');
            $this->redirect('?action=movies');
        }
        set_flash('danger', 'Không thể thêm phim.');
        $this->redirect('?action=create_movie');
    }

    public function edit(int $id): void {
        $movie = $this->movieModel->getById($id);
        if (!$movie) {
            set_flash('danger', 'Không tìm thấy phim cần chỉnh sửa.');
            $this->redirect('?action=movies');
        }
        $this->renderAdmin('edit', [
            'movie' => $movie,
            'genreOptions' => $this->movieModel->getGenreOptions(),
            'activeMenu' => 'movies',
            'breadcrumb' => 'Chỉnh sửa phim',
            'pageTitle' => 'Chỉnh sửa phim',
        ]);
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?action=movies');
        }
        $id = (int) ($_POST['movie_id'] ?? 0);
        $existing = $this->movieModel->getById($id);
        if (!$existing) {
            set_flash('danger', 'Phim không tồn tại.');
            $this->redirect('?action=movies');
        }
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'director' => trim($_POST['director'] ?? ''),
            'cast' => trim($_POST['cast'] ?? ''),
            'genre' => trim($_POST['genre'] ?? ''),
            'duration' => (int) ($_POST['duration'] ?? 0),
            'release_date' => $_POST['release_date'] ?? null,
            'trailer_url' => trim($_POST['trailer_url'] ?? ''),
            'status' => $_POST['status'] ?? 1,
        ];
        $newPoster = $this->uploadImage('poster', 'poster');
        if ($newPoster) $data['poster'] = $newPoster;
        $newBanner = $this->uploadImage('banner', 'banner');
        if ($newBanner) $data['banner'] = $newBanner;

        if ($this->movieModel->update($id, $data)) {
            if ($newPoster && !empty($existing['poster'])) {
                delete_local_file($existing['poster'], [$this->defaultPoster]);
            }
            if ($newBanner && !empty($existing['banner'])) {
                delete_local_file($existing['banner'], [$this->defaultBanner]);
            }
            set_flash('success', 'Cập nhật phim thành công.');
            $this->redirect('?action=movies');
        }

        if ($newPoster) delete_local_file($newPoster, []);
        if ($newBanner) delete_local_file($newBanner, []);
        set_flash('danger', 'Không thể cập nhật phim.');
        $this->redirect('?action=edit_movie&id=' . $id);
    }

    public function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?action=movies');
        }
        $id = (int) ($_POST['movie_id'] ?? 0);
        $movie = $this->movieModel->getById($id);
        if (!$movie) {
            set_flash('danger', 'Không tìm thấy phim cần xóa.');
            $this->redirect('?action=movies');
        }
        if ($this->movieModel->delete($id)) {
            delete_local_file($movie['poster'] ?? null, [$this->defaultPoster]);
            delete_local_file($movie['banner'] ?? null, [$this->defaultBanner]);
            set_flash('success', 'Xóa phim thành công.');
        } else {
            set_flash('danger', 'Không thể xóa phim.');
        }
        $this->redirect('?action=movies');
    }
}
?>
<?php
require_once 'config/database.php';
require_once 'models/Movie.php';
require_once 'models/Order.php';
require_once 'models/Ticket.php';
require_once 'models/Promotion.php';
require_once 'models/SeatPrice.php';

class MovieController {
    private $movieModel;
    private $orderModel;
    private $ticketModel;
    private $promotionModel;
    private $seatPriceModel;

    public function __construct() {
        $this->movieModel = new Movie();
        $this->orderModel = new Order();
        $this->ticketModel = new Ticket();
        $this->promotionModel = new Promotion();
        $this->seatPriceModel = new SeatPrice();
    }

    private function validatePromotionForOrder(array $promo, array $order, array $tickets): array {
        $totalTickets = count($tickets);
        $totalAmount = $order['total_amount'];
        $seatTypes = array_unique(array_map(function ($ticket) {
            return $ticket['seat_type'] ?? 'standard';
        }, $tickets));

        if (!empty($promo['min_tickets']) && $totalTickets < intval($promo['min_tickets'])) {
            return [
                'applicable' => false,
                'reason' => 'Cần ít nhất ' . intval($promo['min_tickets']) . ' vé để áp dụng mã này.'
            ];
        }

        if (!empty($promo['min_amount']) && $totalAmount < floatval($promo['min_amount'])) {
            return [
                'applicable' => false,
                'reason' => 'Đơn hàng phải từ ' . number_format(floatval($promo['min_amount']), 0, ',', '.') . '₫ trở lên.'
            ];
        }

        if (!empty($promo['applicable_seat_types'])) {
            $allowedTypes = json_decode($promo['applicable_seat_types'], true);
            if (is_array($allowedTypes) && count($allowedTypes) > 0) {
                foreach ($seatTypes as $type) {
                    if (!in_array($type, $allowedTypes, true)) {
                        return [
                            'applicable' => false,
                            'reason' => 'Mã này chỉ áp dụng cho ghế: ' . implode(', ', $allowedTypes) . '.'
                        ];
                    }
                }
            }
        }

        return [
            'applicable' => true,
            'reason' => ''
        ];
    }

    public function index() {
        $searchQuery = trim($_GET['q'] ?? '');

        if ($searchQuery !== '') {
            $searchQuery = mb_strtolower($searchQuery, 'UTF-8');
            $searchResults = $this->movieModel->searchMovies($searchQuery);
            $movies = array_values(array_filter($searchResults, function ($movie) {
                return $movie['status'] === 'showing';
            }));
            $comingSoon = array_values(array_filter($searchResults, function ($movie) {
                return $movie['status'] === 'coming_soon';
            }));
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
                list($cinema, $hall) = explode(' - ', $roomName, 2);
            }
            $cinema = trim($cinema);
            $hall = trim($hall);
            if (!isset($theaters[$cinema])) {
                $theaters[$cinema] = [];
            }
            $theaters[$cinema][] = [
                'hall' => $hall,
                'capacity' => intval($room['capacity'])
            ];
        }

        include 'views/movies/home.php';
    }

    public function detail() {
        $movie_id = intval($_GET['id'] ?? 0);
        $movie = $this->movieModel->getMovieById($movie_id);

        if (!$movie) {
            header('Location: index.php');
            exit;
        }

        $showtimes = $this->movieModel->getShowtimesByMovie($movie_id);
        include 'views/movies/detail.php';
    }

    public function selectShowtime() {
        $movie_id = intval($_GET['id'] ?? 0);
        $movie = $this->movieModel->getMovieById($movie_id);

        if (!$movie) {
            header('Location: index.php');
            exit;
        }

        $showtimes = $this->movieModel->getShowtimesByMovie($movie_id);
        include 'views/booking/select-showtime.php';
    }

    public function book() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: web.php?action=login');
            exit;
        }

        $showtime_id = intval($_GET['showtime_id'] ?? 0);
        $showtime = $this->movieModel->getShowtimeById($showtime_id);

        if (!$showtime) {
            header('Location: index.php');
            exit;
        }

        $movie = $this->movieModel->getMovieById($showtime['movie_id']);
        $seatMap = $this->movieModel->getSeatsForShowtime($showtime_id);
        $seatPricesInfo = $this->seatPriceModel->getAll();

        $errors = [];
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $selectedSeats = array_map('intval', $_POST['seats'] ?? []);
            $promoCode = trim($_POST['promo_code'] ?? '');

            if (empty($selectedSeats)) {
                $errors[] = 'Vui lòng chọn ít nhất một ghế.';
            }

            $offer = null;
            $discountAmount = 0.00;
            $promotion_id = null;
            
            // Calculate total amount based on seat types
            $totalAmount = 0;
            $seatPrices = [];
            
            if (empty($errors)) {
                foreach ($seatMap as $seat) {
                    if (in_array(intval($seat['seat_id']), $selectedSeats, true)) {
                        $seatType = $seat['seat_type'] ?? 'standard';
                        $seatPriceInfo = $this->seatPriceModel->getByType($seatType);
                        $multiplier = $seatPriceInfo ? floatval($seatPriceInfo['price_multiplier']) : 1.00;
                        $seatPrice = $showtime['base_price'] * $multiplier;
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
                    if ($offer['discount_type'] === 'percent') {
                        $discountAmount = round($totalAmount * ($offer['discount_value'] / 100), 2);
                    } else {
                        $discountAmount = min($offer['discount_value'], $totalAmount);
                    }
                    $finalAmount = max(0, $totalAmount - $discountAmount);
                }
            }

            if (empty($errors)) {
                $orderCode = 'ORD' . time() . rand(100, 999);
                $order_id = $this->orderModel->create(
                    $_SESSION['user_id'],
                    $promotion_id,
                    $orderCode,
                    $totalAmount,
                    $discountAmount,
                    $finalAmount
                );

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

        include 'views/booking/book.php';
    }

    public function checkout() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: web.php?action=login');
            exit;
        }

        $order_id = intval($_GET['order_id'] ?? 0);
        $order = $this->orderModel->getById($order_id);
        $errors = [];
        $success = '';
        $promoCode = $order['promo_code'] ?? '';
        $activePromotions = $this->promotionModel->getActivePromotions();

        if (!$order || $order['user_id'] != $_SESSION['user_id']) {
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
                if (!empty($_POST['apply_promo_code'])) {
                    $promoCode = trim($_POST['apply_promo_code']);
                } else {
                    $promoCode = trim($_POST['promo_code'] ?? '');
                }

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
                            if ($offer['discount_type'] === 'percent') {
                                $discountAmount = round($totalAmount * ($offer['discount_value'] / 100), 2);
                            } else {
                                $discountAmount = min($offer['discount_value'], $totalAmount);
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
                if ($this->orderModel->updateStatus($order_id, 'paid') && $this->ticketModel->markPaid($order_id)) {
                    header('Location: web.php?action=booking-success&order_id=' . $order_id);
                    exit;
                }

                $errors[] = 'Không thể cập nhật đơn hàng. Vui lòng thử lại.';
            }
        }

        include 'views/booking/checkout.php';
    }

    public function success() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: web.php?action=login');
            exit;
        }

        $order_id = intval($_GET['order_id'] ?? 0);
        $order = $this->orderModel->getById($order_id);

        if (!$order || $order['user_id'] != $_SESSION['user_id']) {
            header('Location: web.php?action=booking-history');
            exit;
        }

        $tickets = $this->ticketModel->getTicketsByOrder($order_id);
        include 'views/booking/success.php';
    }

    public function theaters() {
        $allRooms = $this->movieModel->getAllRooms();
        $theaters = [];
        foreach ($allRooms as $room) {
            $roomName = trim($room['room_name']);
            $cinema = 'Rạp khác';
            $hall = $roomName;
            if (strpos($roomName, ' - ') !== false) {
                list($cinema, $hall) = explode(' - ', $roomName, 2);
            }
            $cinema = trim($cinema);
            $hall = trim($hall);
            if (!isset($theaters[$cinema])) {
                $theaters[$cinema] = [];
            }
            $theaters[$cinema][] = [
                'hall' => $hall,
                'capacity' => intval($room['capacity'])
            ];
        }
        include 'views/movies/theaters.php';
    }

    public function promotions() {
        $activePromotions = $this->promotionModel->getActivePromotions();
        include 'views/movies/promotions.php';
    }
}


<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php if (empty($searchQuery)): ?>
    <div class="home-hero">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <div class="hero-info">
                <span class="eyebrow hero-eyebrow">PHIM ĐANG HOT</span>
                <h1>Nhà Bà Nữ</h1>
                <p>Phim xoay quanh gia đình bà Nữ ba thế hệ sống chung, nảy sinh nhiều mâu thuẫn khi con gái út yêu chàng trai giàu có, qua đó khắc họa những xung đột gia đình với thông điệp: “Ai cũng có lỗi, nhưng ai cũng nghĩ mình là nạn nhân.”</p>
                <div class="hero-actions">
                    <a href="#now-showing" class="btn btn-primary">Đặt vé ngay</a>
                    <button type="button" class="btn btn-secondary btn-secondary--light open-trailer" data-trailer="https://www.youtube.com/embed/IkaP0KJWTsQ">Xem trailer</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="trailer-modal" id="trailerModal" aria-hidden="true">
    <div class="trailer-modal-backdrop" id="trailerBackdrop"></div>
    <div class="trailer-modal-content" role="dialog" aria-modal="true" aria-labelledby="trailerTitle">
        <div class="trailer-modal-header">
            <h2 id="trailerTitle">Xem trailer</h2>
            <button type="button" class="trailer-close" id="trailerClose" aria-label="Đóng">×</button>
        </div>
        <div class="trailer-video">
            <iframe id="trailerIframe" src="" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
    </div>
</div>

<?php if (!empty($searchQuery)): ?>
    <div class="search-results-banner">
        <div class="container">
            <p>Kết quả tìm kiếm cho <strong>"<?php echo htmlspecialchars($searchQuery); ?>"</strong></p>
        </div>
    </div>
<?php endif; ?>

<div class="page-section" id="now-showing">
    <div class="container">
        <div class="section-header section-spacer">
            <div class="section-title">
                <span class="eyebrow">PHIM ĐANG CHIẾU</span>
                <h2>Phim đang chiếu</h2>
            </div>
        </div>

        <?php if (!empty($movies)): ?>
            <div class="movie-grid">
                <?php foreach ($movies as $movie):
                    $posterUrl = htmlspecialchars(getPosterUrl($movie['poster_url'] ?? ''));
                ?>
                    <article class="movie-card">
                        <div class="movie-poster">
                            <img src="<?php echo $posterUrl; ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <div class="movie-poster-overlay"></div>
                            <div class="movie-poster-badge">
                                <span><?php echo htmlspecialchars($movie['genre']); ?></span>
                            </div>
                        </div>
                        <div class="movie-content">
                            <h2><?php echo htmlspecialchars($movie['title']); ?></h2>
                            <p class="movie-meta"><?php echo htmlspecialchars($movie['genre']); ?> • <?php echo intval($movie['duration']); ?> phút</p>
                            <p class="movie-description"><?php echo htmlspecialchars(mb_substr($movie['description'] ?? '', 0, 120)); ?><?php echo mb_strlen($movie['description'] ?? '') > 120 ? '...' : ''; ?></p>
                            <div class="movie-actions">
                                <a href="<?= h(app_url('movie', ['id' => $movie['movie_id']])) ?>" class="btn btn-secondary">Đặt vé</a>
                                <button type="button" class="btn btn-secondary btn-secondary--light open-trailer" data-trailer="<?php echo htmlspecialchars($movie['trailer_url'] ?? ''); ?>">Xem trailer</button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>Không có phim nào đang chiếu vào lúc này. Vui lòng quay lại sau.</p>
            </div>
        <?php endif; ?>

        <div class="section-header section-spacer" id="coming-soon">
            <div class="section-title">
                <span class="eyebrow">PHIM SẮP CHIẾU</span>
                <h2>Phim sắp chiếu</h2>
            </div>
        </div>
        <div class="coming-soon-grid">
            <?php if (!empty($comingSoon)): ?>
                <?php foreach ($comingSoon as $movie):
                    $posterUrl = htmlspecialchars(getPosterUrl($movie['poster_url'] ?? ''));
                    $releaseDate = strtotime($movie['release_date'] ?? '');
                    $releaseLabel = $releaseDate ? sprintf('%02d Tháng %d', date('d', $releaseDate), date('n', $releaseDate)) : 'Sắp chiếu';
                ?>
                    <article class="coming-soon-card">
                        <div class="coming-soon-poster">
                            <img src="<?php echo $posterUrl; ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <div class="coming-soon-overlay"></div>
                            <span class="coming-soon-date"><?php echo $releaseLabel; ?></span>
                            <div class="coming-soon-caption">
                                <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
                                <p class="coming-soon-description"><?php echo htmlspecialchars(mb_substr($movie['description'] ?? '', 0, 100)); ?>...</p>
                                <button type="button" class="btn btn-secondary btn-secondary--light open-trailer coming-soon-trailer" data-trailer="<?php echo htmlspecialchars($movie['trailer_url'] ?? ''); ?>">Xem trailer</button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>Hiện chưa có phim sắp chiếu. Vui lòng quay lại sau.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const trailerModal = document.getElementById('trailerModal');
        const trailerIframe = document.getElementById('trailerIframe');
        const trailerClose = document.getElementById('trailerClose');
        const trailerBackdrop = document.getElementById('trailerBackdrop');

        function getAutoplayUrl(url) {
            if (!url) return '';
            return url.includes('?') ? url + '&autoplay=1' : url + '?autoplay=1';
        }

        function openTrailer(trailerUrl) {
            if (!trailerUrl) {
                alert('Trailer hiện chưa có sẵn.');
                return;
            }
            trailerIframe.src = getAutoplayUrl(trailerUrl);
            trailerModal.classList.add('open');
            trailerModal.setAttribute('aria-hidden', 'false');
        }

        function closeTrailer() {
            trailerIframe.src = '';
            trailerModal.classList.remove('open');
            trailerModal.setAttribute('aria-hidden', 'true');
        }

        document.querySelectorAll('.open-trailer').forEach(function(button) {
            button.addEventListener('click', function() {
                openTrailer(this.getAttribute('data-trailer'));
            });
        });

        trailerClose.addEventListener('click', closeTrailer);
        trailerBackdrop.addEventListener('click', closeTrailer);
        window.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && trailerModal.classList.contains('open')) {
                closeTrailer();
            }
        });
    </script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
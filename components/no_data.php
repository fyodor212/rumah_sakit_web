<?php
/**
 * Komponen untuk menampilkan pesan ketika tidak ada data
 * @param string $message Pesan yang akan ditampilkan
 * @param string $icon Icon yang akan ditampilkan (default: question-circle)
 * @param string $class Class tambahan untuk container (opsional)
 */
function showNoData($message = 'Tidak ada data', $icon = 'question-circle', $class = '') {
    ?>
    <div class="text-center py-5 <?= $class ?>">
        <div class="mb-3">
            <i class="fas fa-<?= $icon ?> text-muted" style="font-size: 4rem;"></i>
        </div>
        <h5 class="text-muted mb-3"><?= $message ?></h5>
        <p class="text-muted mb-0">Silakan coba dengan filter yang berbeda</p>
    </div>
    <?php
}
?> 
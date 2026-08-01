<?php
$__ticker = [
    'Free shipping over ₹2,000',
    'New drop every friday',
    'Easy 30-day returns',
    'Train with STRIDE',
];
?>
    <div class="ticker">
        <div class="ticker-track">
            <?php for ($i = 0; $i < 2; $i++): foreach ($__ticker as $msg): ?>
                <span><?php echo htmlspecialchars($msg); ?></span>
            <?php endforeach; endfor; ?>
        </div>
    </div>

<?php
$__ticker = [
    'Free express shipping over ₹2,000',
    'Engineered for speed & endurance',
    'Easy 30-day returns',
    'Train beyond limits with ElevenX',
];
?>
    <div class="ticker">
        <div class="ticker-track">
            <?php for ($i = 0; $i < 2; $i++): foreach ($__ticker as $msg): ?>
                <span><?php echo htmlspecialchars($msg); ?></span>
            <?php endforeach; endfor; ?>
        </div>
    </div>

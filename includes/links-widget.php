<?php
// Add or remove links here. Edit the titles to anything you like.
$usefulLinks = [
    ['title' => 'Al-Mizan',     'url' => 'https://www.almizan.org',   'icon' => 'fa-globe'],
    ['title' => 'Quran (MMSY)', 'url' => 'https://quran.mmsy.info/',  'icon' => 'fa-book-quran'],
];
?>
<div class="rounded-xl shadow-sm p-4 mt-4"
     style="background: var(--color-card); border: 1px solid var(--color-border)">
    <h3 class="font-bold text-sm uppercase tracking-wide mb-4 flex items-center gap-2"
        style="color: var(--color-text)">
        <i class="fas fa-link" style="color: var(--color-gold)"></i>
        Useful Links
    </h3>
    <div class="space-y-3">
        <?php foreach ($usefulLinks as $link): ?>
        <?php $host = preg_replace('/^www\./', '', parse_url($link['url'], PHP_URL_HOST)); ?>
        <a href="<?= clean($link['url']) ?>"
           target="_blank" rel="noopener noreferrer"
           class="flex items-center gap-3"
           style="text-decoration: none;">
            <div class="w-9 h-9 rounded-lg flex-shrink-0 flex items-center justify-center"
                 style="background: var(--color-primary)">
                <i class="fas <?= clean($link['icon']) ?> text-white text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium truncate"
                   style="color: var(--color-text);"
                   onmouseover="this.style.color='var(--color-primary)'"
                   onmouseout="this.style.color='var(--color-text)'">
                    <?= clean($link['title']) ?>
                </p>
                <p class="text-xs truncate" style="color: var(--color-text-muted)">
                    <?= clean($host) ?>
                </p>
            </div>
            <i class="fas fa-arrow-up-right-from-square text-xs"
               style="color: var(--color-text-muted)"></i>
        </a>
        <?php endforeach; ?>
    </div>
</div>
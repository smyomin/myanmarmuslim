<!-- Footer -->
<footer class="site-footer mt-16">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <!-- About -->
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <img src="<?= SITE_URL ?>/assets/images/logowhite.png" alt="Myanmar Muslim Logo" class="w-12 h-12 object-cover">
                    <span class="font-bold text-lg">Myanmar Muslim</span>
                </div>
                <p class="text-gray-300 text-sm leading-relaxed">
                    ကမ္ဘာအရပ်ရပ်ရှိ မြန်မာမွတ်စလင်များအတွက် အီးဘွတ်ခ်များ၊ ဒိုအာများ၊ သုတေသနစာတမ်းများနှင့် အခြားအရာများကို ပံ့ပိုးပေးနေသည့် အခမဲ့ အွန်လိုင်းအစ္စလာမ့်စာကြည့်တိုက်။
                </p>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="font-bold text-gold mb-4">Quick Links</h3>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li><a href="<?= SITE_URL ?>/pages/library.php" class="hover:text-white transition">📚 စာကြည့်တိုက်</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/duas.php" class="hover:text-white transition">🤲 ဒိုအာနှင့်အအ်မာလ်များ</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/news.php" class="hover:text-white transition">📰 သတင်းဆောင်းပါးများ</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/about.php" class="hover:text-white transition">ℹ️ ကျွန်ုပ်တို့အကြောင်း</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/contact.php" class="hover:text-white transition">✉️ ဆက်သွယ်ရန်</a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div>
                <h3 class="font-bold text-gold mb-4">အဆက်အသွယ်မပြတ်စေရန်</h3>
                <p class="text-gray-300 text-sm mb-3">စာအုပ်အသစ်များ ထွက်ရှိသည့်အခါ အကြောင်းကြားစာရယူရန် Subscribe လုပ်ထားပါ။.</p>
                <form action="<?= SITE_URL ?>/includes/subscribe.php" method="POST" class="flex gap-2">
                    <input type="email" name="email" placeholder="Your email address"
                           class="flex-1 px-3 py-2 rounded text-gray-800 text-sm focus:outline-none" required>
                    <button type="submit"
                            class="bg-gold hover:bg-gold-dark text-white px-4 py-2 rounded text-sm font-medium transition">
                        Subscribe
                    </button>
                </form>
                <div id="subscribeMsg" class="text-sm mt-2 text-green-300 hidden">✅ Subscribed successfully!</div>
            </div>
        </div>

        <div class="border-t border-green-800 mt-8 pt-6 text-center text-gray-400 text-sm">
            <p>&copy; <?= date('Y') ?> Myanmar Muslim. All rights reserved. Made with ❤️ for the Ummah.</p>
        </div>
    </div>
</footer>
</body>
</html>
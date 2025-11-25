const CACHE_NAME = 'investocc-v2.5.1';
const ASSETS_TO_CACHE = [
    '/app/assets/images/logo/logo-icon.png',
    '/app/assets/images/logo/app-icon.png',
    '/app/assets/images/logo/logo-icon-180.png',
    '/app/assets/images/icons/avatar.jpg',
    '/app/assets/images/my-icons/fixed-returns-tab-icon.png',
    '/app/assets/images/my-icons/investment.png',
    '/app/assets/images/my-icons/portfolio-tab-icon.png',
    '/app/assets/images/my-icons/stockorder.png',
    '/app/assets/images/my-icons/stocks-tab-icon.png',
    '/app/assets/images/my-icons/withdrawal.png',
    '/app/assets/images/crypto/bank.png',
    '/app/assets/images/crypto/bnb.png',
    '/app/assets/images/crypto/btc.png',
    '/app/assets/images/crypto/eth.png',
    '/app/assets/images/crypto/flutterwave.png',
    '/app/assets/images/crypto/paystack.png',
    '/app/assets/images/crypto/opay.png',
    '/app/assets/images/crypto/sol.png',
    '/app/assets/images/crypto/usdt.png',
    '/app/assets/images/icons/background.jpg',
    '/app/assets/images/background/bg.jpg',
    '/app/assets/css/distv2.5.1/style.css',
    '/app/assets/css/distv2.5.1/more-styles.css',
    '/app/assets/vendor/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css',
    '/app/assets/vendor/swiper/swiper-bundle.min.css',
    '/app/assets/js/jquery.js',
    '/app/assets/js/qrcode.min.js',
    '/app/assets/vendor/bootstrap/js/bootstrap.bundle.min.js',
    '/app/assets/vendor/apexcharts/dist/apexcharts.js',
    '/app/',
    '/app/index.html',
    '/app/distv2.5.1/1066.bundle.js',
    '/app/distv2.5.1/1140.bundle.js',
    '/app/distv2.5.1/124.bundle.js',
    '/app/distv2.5.1/1278.bundle.js',
    '/app/distv2.5.1/1283.bundle.js',
    '/app/distv2.5.1/1435.bundle.js',
    '/app/distv2.5.1/1652.bundle.js',
    '/app/distv2.5.1/1653.bundle.js',
    '/app/distv2.5.1/1684.bundle.js',
    '/app/distv2.5.1/1694.bundle.js',
    '/app/distv2.5.1/1757.bundle.js',
    '/app/distv2.5.1/1817.bundle.js',
    '/app/distv2.5.1/1956.bundle.js',
    '/app/distv2.5.1/2022.bundle.js',
    '/app/distv2.5.1/2452.bundle.js',
    '/app/distv2.5.1/2528.bundle.js',
    '/app/distv2.5.1/2551.bundle.js',
    '/app/distv2.5.1/2648.bundle.js',
    '/app/distv2.5.1/2691.bundle.js',
    '/app/distv2.5.1/2718.bundle.js',
    '/app/distv2.5.1/3033.bundle.js',
    '/app/distv2.5.1/3155.bundle.js',
    '/app/distv2.5.1/3192.bundle.js',
    '/app/distv2.5.1/3253.bundle.js',
    '/app/distv2.5.1/3284.bundle.js',
    '/app/distv2.5.1/3296.bundle.js',
    '/app/distv2.5.1/3414.bundle.js',
    '/app/distv2.5.1/350.bundle.js',
    '/app/distv2.5.1/3633.bundle.js',
    '/app/distv2.5.1/3706.bundle.js',
    '/app/distv2.5.1/3750.bundle.js',
    '/app/distv2.5.1/3782.bundle.js',
    '/app/distv2.5.1/3798.bundle.js',
    '/app/distv2.5.1/380.bundle.js',
    '/app/distv2.5.1/4186.bundle.js',
    '/app/distv2.5.1/4285.bundle.js',
    '/app/distv2.5.1/4509.bundle.js',
    '/app/distv2.5.1/4565.bundle.js',
    '/app/distv2.5.1/4587.bundle.js',
    '/app/distv2.5.1/4815.bundle.js',
    '/app/distv2.5.1/4826.bundle.js',
    '/app/distv2.5.1/488.bundle.js',
    '/app/distv2.5.1/4921.bundle.js',
    '/app/distv2.5.1/4932.bundle.js',
    '/app/distv2.5.1/4977.bundle.js',
    '/app/distv2.5.1/5145.bundle.js',
    '/app/distv2.5.1/5165.bundle.js',
    '/app/distv2.5.1/5166.bundle.js',
    '/app/distv2.5.1/5289.bundle.js',
    '/app/distv2.5.1/5301.bundle.js',
    '/app/distv2.5.1/5344.bundle.js',
    '/app/distv2.5.1/5410.bundle.js',
    '/app/distv2.5.1/5482.bundle.js',
    '/app/distv2.5.1/5529.bundle.js',
    '/app/distv2.5.1/5616.bundle.js',
    '/app/distv2.5.1/5642.bundle.js',
    '/app/distv2.5.1/5692.bundle.js',
    '/app/distv2.5.1/5716.bundle.js',
    '/app/distv2.5.1/608.bundle.js',
    '/app/distv2.5.1/6161.bundle.js',
    '/app/distv2.5.1/6183.bundle.js',
    '/app/distv2.5.1/6225.bundle.js',
    '/app/distv2.5.1/6261.bundle.js',
    '/app/distv2.5.1/6402.bundle.js',
    '/app/distv2.5.1/6548.bundle.js',
    '/app/distv2.5.1/6590.bundle.js',
    '/app/distv2.5.1/667.bundle.js',
    '/app/distv2.5.1/6675.bundle.js',
    '/app/distv2.5.1/6778.bundle.js',
    '/app/distv2.5.1/691.bundle.js',
    '/app/distv2.5.1/6984.bundle.js',
    '/app/distv2.5.1/7123.bundle.js',
    '/app/distv2.5.1/7145.bundle.js',
    '/app/distv2.5.1/7206.bundle.js',
    '/app/distv2.5.1/7214.bundle.js',
    '/app/distv2.5.1/725.bundle.js',
    '/app/distv2.5.1/7372.bundle.js',
    '/app/distv2.5.1/7428.bundle.js',
    '/app/distv2.5.1/7483.bundle.js',
    '/app/distv2.5.1/7486.bundle.js',
    '/app/distv2.5.1/7821.bundle.js',
    '/app/distv2.5.1/7875.bundle.js',
    '/app/distv2.5.1/8069.bundle.js',
    '/app/distv2.5.1/8305.bundle.js',
    '/app/distv2.5.1/8348.bundle.js',
    '/app/distv2.5.1/8367.bundle.js',
    '/app/distv2.5.1/8642.bundle.js',
    '/app/distv2.5.1/8664.bundle.js',
    '/app/distv2.5.1/8747.bundle.js',
    '/app/distv2.5.1/8773.bundle.js',
    '/app/distv2.5.1/8789.bundle.js',
    '/app/distv2.5.1/8863.bundle.js',
    '/app/distv2.5.1/8890.bundle.js',
    '/app/distv2.5.1/9035.bundle.js',
    '/app/distv2.5.1/9071.bundle.js',
    '/app/distv2.5.1/9089.bundle.js',
    '/app/distv2.5.1/9179.bundle.js',
    '/app/distv2.5.1/9649.bundle.js',
    '/app/distv2.5.1/9675.bundle.js',
    '/app/distv2.5.1/972.bundle.js',
    '/app/distv2.5.1/bundle.js'
];

// Install: cache assets
self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS_TO_CACHE))
  );
});

// Activate: delete old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys.map(key => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      )
    ).then(() => self.clients.claim())
  );
});

// Fetch: try cache first, fallback to network
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request).then(response =>
      response || fetch(event.request)
    )
  );
});
const CACHE_NAME = 'investocc-v1.9.5';
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
    '/app/assets/css/distv1.9.5/style.css',
    '/app/assets/css/distv1.9.5/more-styles.css',
    '/app/assets/vendor/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css',
    '/app/assets/vendor/swiper/swiper-bundle.min.css',
    '/app/assets/js/jquery.js',
    '/app/assets/js/qrcode.min.js',
    '/app/assets/vendor/bootstrap/js/bootstrap.bundle.min.js',
    '/app/assets/vendor/apexcharts/dist/apexcharts.js',
    '/app/',
    '/app/index.html',
    '/app/distv1.9.5/1066.bundle.js',
    '/app/distv1.9.5/1140.bundle.js',
    '/app/distv1.9.5/124.bundle.js',
    '/app/distv1.9.5/1278.bundle.js',
    '/app/distv1.9.5/1283.bundle.js',
    '/app/distv1.9.5/1435.bundle.js',
    '/app/distv1.9.5/1652.bundle.js',
    '/app/distv1.9.5/1653.bundle.js',
    '/app/distv1.9.5/1684.bundle.js',
    '/app/distv1.9.5/1694.bundle.js',
    '/app/distv1.9.5/1757.bundle.js',
    '/app/distv1.9.5/1817.bundle.js',
    '/app/distv1.9.5/1956.bundle.js',
    '/app/distv1.9.5/2022.bundle.js',
    '/app/distv1.9.5/2170.bundle.js',
    '/app/distv1.9.5/2452.bundle.js',
    '/app/distv1.9.5/2528.bundle.js',
    '/app/distv1.9.5/2551.bundle.js',
    '/app/distv1.9.5/2648.bundle.js',
    '/app/distv1.9.5/2691.bundle.js',
    '/app/distv1.9.5/2718.bundle.js',
    '/app/distv1.9.5/3033.bundle.js',
    '/app/distv1.9.5/3192.bundle.js',
    '/app/distv1.9.5/3253.bundle.js',
    '/app/distv1.9.5/3284.bundle.js',
    '/app/distv1.9.5/3296.bundle.js',
    '/app/distv1.9.5/3414.bundle.js',
    '/app/distv1.9.5/350.bundle.js',
    '/app/distv1.9.5/3633.bundle.js',
    '/app/distv1.9.5/3706.bundle.js',
    '/app/distv1.9.5/3750.bundle.js',
    '/app/distv1.9.5/3782.bundle.js',
    '/app/distv1.9.5/3798.bundle.js',
    '/app/distv1.9.5/380.bundle.js',
    '/app/distv1.9.5/4186.bundle.js',
    '/app/distv1.9.5/4285.bundle.js',
    '/app/distv1.9.5/4509.bundle.js',
    '/app/distv1.9.5/4565.bundle.js',
    '/app/distv1.9.5/4587.bundle.js',
    '/app/distv1.9.5/4815.bundle.js',
    '/app/distv1.9.5/4826.bundle.js',
    '/app/distv1.9.5/488.bundle.js',
    '/app/distv1.9.5/4921.bundle.js',
    '/app/distv1.9.5/4932.bundle.js',
    '/app/distv1.9.5/4977.bundle.js',
    '/app/distv1.9.5/5145.bundle.js',
    '/app/distv1.9.5/5165.bundle.js',
    '/app/distv1.9.5/5166.bundle.js',
    '/app/distv1.9.5/5289.bundle.js',
    '/app/distv1.9.5/5301.bundle.js',
    '/app/distv1.9.5/5344.bundle.js',
    '/app/distv1.9.5/5410.bundle.js',
    '/app/distv1.9.5/5482.bundle.js',
    '/app/distv1.9.5/5529.bundle.js',
    '/app/distv1.9.5/5616.bundle.js',
    '/app/distv1.9.5/5692.bundle.js',
    '/app/distv1.9.5/5716.bundle.js',
    '/app/distv1.9.5/608.bundle.js',
    '/app/distv1.9.5/6161.bundle.js',
    '/app/distv1.9.5/6183.bundle.js',
    '/app/distv1.9.5/6225.bundle.js',
    '/app/distv1.9.5/6261.bundle.js',
    '/app/distv1.9.5/6402.bundle.js',
    '/app/distv1.9.5/6548.bundle.js',
    '/app/distv1.9.5/6590.bundle.js',
    '/app/distv1.9.5/667.bundle.js',
    '/app/distv1.9.5/6675.bundle.js',
    '/app/distv1.9.5/6778.bundle.js',
    '/app/distv1.9.5/691.bundle.js',
    '/app/distv1.9.5/6984.bundle.js',
    '/app/distv1.9.5/7123.bundle.js',
    '/app/distv1.9.5/7145.bundle.js',
    '/app/distv1.9.5/7206.bundle.js',
    '/app/distv1.9.5/7214.bundle.js',
    '/app/distv1.9.5/725.bundle.js',
    '/app/distv1.9.5/7372.bundle.js',
    '/app/distv1.9.5/7428.bundle.js',
    '/app/distv1.9.5/7483.bundle.js',
    '/app/distv1.9.5/7486.bundle.js',
    '/app/distv1.9.5/7821.bundle.js',
    '/app/distv1.9.5/8348.bundle.js',
    '/app/distv1.9.5/8367.bundle.js',
    '/app/distv1.9.5/8642.bundle.js',
    '/app/distv1.9.5/8664.bundle.js',
    '/app/distv1.9.5/8706.bundle.js',
    '/app/distv1.9.5/8747.bundle.js',
    '/app/distv1.9.5/8773.bundle.js',
    '/app/distv1.9.5/8789.bundle.js',
    '/app/distv1.9.5/8797.bundle.js',
    '/app/distv1.9.5/8863.bundle.js',
    '/app/distv1.9.5/8890.bundle.js',
    '/app/distv1.9.5/9035.bundle.js',
    '/app/distv1.9.5/9071.bundle.js',
    '/app/distv1.9.5/9089.bundle.js',
    '/app/distv1.9.5/9179.bundle.js',
    '/app/distv1.9.5/9649.bundle.js',
    '/app/distv1.9.5/9675.bundle.js',
    '/app/distv1.9.5/972.bundle.js',
    '/app/distv1.9.5/bundle.js'
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
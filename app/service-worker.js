const CACHE_NAME = 'investocc-v1.9.9';
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
    '/app/assets/css/distv1.9.9/style.css',
    '/app/assets/css/distv1.9.9/more-styles.css',
    '/app/assets/vendor/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css',
    '/app/assets/vendor/swiper/swiper-bundle.min.css',
    '/app/assets/js/jquery.js',
    '/app/assets/js/qrcode.min.js',
    '/app/assets/vendor/bootstrap/js/bootstrap.bundle.min.js',
    '/app/assets/vendor/apexcharts/dist/apexcharts.js',
    '/app/',
    '/app/index.html',
    '/app/distv1.9.9/1066.bundle.js',
    '/app/distv1.9.9/1140.bundle.js',
    '/app/distv1.9.9/124.bundle.js',
    '/app/distv1.9.9/1278.bundle.js',
    '/app/distv1.9.9/1283.bundle.js',
    '/app/distv1.9.9/1435.bundle.js',
    '/app/distv1.9.9/1652.bundle.js',
    '/app/distv1.9.9/1653.bundle.js',
    '/app/distv1.9.9/1684.bundle.js',
    '/app/distv1.9.9/1694.bundle.js',
    '/app/distv1.9.9/1757.bundle.js',
    '/app/distv1.9.9/1817.bundle.js',
    '/app/distv1.9.9/1956.bundle.js',
    '/app/distv1.9.9/2022.bundle.js',
    '/app/distv1.9.9/2452.bundle.js',
    '/app/distv1.9.9/2528.bundle.js',
    '/app/distv1.9.9/2551.bundle.js',
    '/app/distv1.9.9/2648.bundle.js',
    '/app/distv1.9.9/2691.bundle.js',
    '/app/distv1.9.9/2718.bundle.js',
    '/app/distv1.9.9/3033.bundle.js',
    '/app/distv1.9.9/3155.bundle.js',
    '/app/distv1.9.9/3192.bundle.js',
    '/app/distv1.9.9/3253.bundle.js',
    '/app/distv1.9.9/3284.bundle.js',
    '/app/distv1.9.9/3296.bundle.js',
    '/app/distv1.9.9/3414.bundle.js',
    '/app/distv1.9.9/350.bundle.js',
    '/app/distv1.9.9/3633.bundle.js',
    '/app/distv1.9.9/3706.bundle.js',
    '/app/distv1.9.9/3750.bundle.js',
    '/app/distv1.9.9/3782.bundle.js',
    '/app/distv1.9.9/3798.bundle.js',
    '/app/distv1.9.9/380.bundle.js',
    '/app/distv1.9.9/4186.bundle.js',
    '/app/distv1.9.9/4285.bundle.js',
    '/app/distv1.9.9/4509.bundle.js',
    '/app/distv1.9.9/4565.bundle.js',
    '/app/distv1.9.9/4587.bundle.js',
    '/app/distv1.9.9/4815.bundle.js',
    '/app/distv1.9.9/4826.bundle.js',
    '/app/distv1.9.9/488.bundle.js',
    '/app/distv1.9.9/4921.bundle.js',
    '/app/distv1.9.9/4932.bundle.js',
    '/app/distv1.9.9/4977.bundle.js',
    '/app/distv1.9.9/5145.bundle.js',
    '/app/distv1.9.9/5165.bundle.js',
    '/app/distv1.9.9/5166.bundle.js',
    '/app/distv1.9.9/5289.bundle.js',
    '/app/distv1.9.9/5301.bundle.js',
    '/app/distv1.9.9/5344.bundle.js',
    '/app/distv1.9.9/5410.bundle.js',
    '/app/distv1.9.9/5482.bundle.js',
    '/app/distv1.9.9/5529.bundle.js',
    '/app/distv1.9.9/5616.bundle.js',
    '/app/distv1.9.9/5692.bundle.js',
    '/app/distv1.9.9/5716.bundle.js',
    '/app/distv1.9.9/608.bundle.js',
    '/app/distv1.9.9/6161.bundle.js',
    '/app/distv1.9.9/6183.bundle.js',
    '/app/distv1.9.9/6225.bundle.js',
    '/app/distv1.9.9/6261.bundle.js',
    '/app/distv1.9.9/6402.bundle.js',
    '/app/distv1.9.9/6548.bundle.js',
    '/app/distv1.9.9/6590.bundle.js',
    '/app/distv1.9.9/667.bundle.js',
    '/app/distv1.9.9/6675.bundle.js',
    '/app/distv1.9.9/6778.bundle.js',
    '/app/distv1.9.9/691.bundle.js',
    '/app/distv1.9.9/6984.bundle.js',
    '/app/distv1.9.9/7123.bundle.js',
    '/app/distv1.9.9/7145.bundle.js',
    '/app/distv1.9.9/7206.bundle.js',
    '/app/distv1.9.9/7214.bundle.js',
    '/app/distv1.9.9/725.bundle.js',
    '/app/distv1.9.9/7372.bundle.js',
    '/app/distv1.9.9/7428.bundle.js',
    '/app/distv1.9.9/7483.bundle.js',
    '/app/distv1.9.9/7486.bundle.js',
    '/app/distv1.9.9/7821.bundle.js',
    '/app/distv1.9.9/7875.bundle.js',
    '/app/distv1.9.9/8069.bundle.js',
    '/app/distv1.9.9/8348.bundle.js',
    '/app/distv1.9.9/8367.bundle.js',
    '/app/distv1.9.9/8642.bundle.js',
    '/app/distv1.9.9/8664.bundle.js',
    '/app/distv1.9.9/8747.bundle.js',
    '/app/distv1.9.9/8773.bundle.js',
    '/app/distv1.9.9/8789.bundle.js',
    '/app/distv1.9.9/8797.bundle.js',
    '/app/distv1.9.9/8863.bundle.js',
    '/app/distv1.9.9/8890.bundle.js',
    '/app/distv1.9.9/9035.bundle.js',
    '/app/distv1.9.9/9071.bundle.js',
    '/app/distv1.9.9/9089.bundle.js',
    '/app/distv1.9.9/9179.bundle.js',
    '/app/distv1.9.9/9649.bundle.js',
    '/app/distv1.9.9/9675.bundle.js',
    '/app/distv1.9.9/972.bundle.js',
    '/app/distv1.9.9/bundle.js'
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
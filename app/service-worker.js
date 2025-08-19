const CACHE_NAME = 'investocc-v1.8.4';
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
    '/app/assets/css/distv1.8.4/style.css',
    '/app/assets/css/distv1.8.4/more-styles.css',
    '/app/assets/vendor/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css',
    '/app/assets/vendor/swiper/swiper-bundle.min.css',
    '/app/assets/js/jquery.js',
    '/app/assets/js/qrcode.min.js',
    '/app/assets/vendor/bootstrap/js/bootstrap.bundle.min.js',
    '/app/assets/vendor/apexcharts/dist/apexcharts.js',
    '/app/',
    '/app/index.html',
    '/app/distv1.8.4/1066.bundle.js',
    '/app/distv1.8.4/1140.bundle.js',
    '/app/distv1.8.4/1278.bundle.js',
    '/app/distv1.8.4/1283.bundle.js',
    '/app/distv1.8.4/1435.bundle.js',
    '/app/distv1.8.4/1652.bundle.js',
    '/app/distv1.8.4/1653.bundle.js',
    '/app/distv1.8.4/1684.bundle.js',
    '/app/distv1.8.4/1694.bundle.js',
    '/app/distv1.8.4/1757.bundle.js',
    '/app/distv1.8.4/1817.bundle.js',
    '/app/distv1.8.4/1956.bundle.js',
    '/app/distv1.8.4/2022.bundle.js',
    '/app/distv1.8.4/2170.bundle.js',
    '/app/distv1.8.4/2452.bundle.js',
    '/app/distv1.8.4/2528.bundle.js',
    '/app/distv1.8.4/2551.bundle.js',
    '/app/distv1.8.4/2648.bundle.js',
    '/app/distv1.8.4/2691.bundle.js',
    '/app/distv1.8.4/2718.bundle.js',
    '/app/distv1.8.4/3033.bundle.js',
    '/app/distv1.8.4/3192.bundle.js',
    '/app/distv1.8.4/3253.bundle.js',
    '/app/distv1.8.4/3284.bundle.js',
    '/app/distv1.8.4/3296.bundle.js',
    '/app/distv1.8.4/3414.bundle.js',
    '/app/distv1.8.4/350.bundle.js',
    '/app/distv1.8.4/3633.bundle.js',
    '/app/distv1.8.4/3706.bundle.js',
    '/app/distv1.8.4/3750.bundle.js',
    '/app/distv1.8.4/3782.bundle.js',
    '/app/distv1.8.4/3798.bundle.js',
    '/app/distv1.8.4/380.bundle.js',
    '/app/distv1.8.4/4186.bundle.js',
    '/app/distv1.8.4/4509.bundle.js',
    '/app/distv1.8.4/4565.bundle.js',
    '/app/distv1.8.4/4587.bundle.js',
    '/app/distv1.8.4/4815.bundle.js',
    '/app/distv1.8.4/4826.bundle.js',
    '/app/distv1.8.4/488.bundle.js',
    '/app/distv1.8.4/4921.bundle.js',
    '/app/distv1.8.4/4932.bundle.js',
    '/app/distv1.8.4/4977.bundle.js',
    '/app/distv1.8.4/5145.bundle.js',
    '/app/distv1.8.4/5165.bundle.js',
    '/app/distv1.8.4/5166.bundle.js',
    '/app/distv1.8.4/5289.bundle.js',
    '/app/distv1.8.4/5301.bundle.js',
    '/app/distv1.8.4/5344.bundle.js',
    '/app/distv1.8.4/5410.bundle.js',
    '/app/distv1.8.4/5482.bundle.js',
    '/app/distv1.8.4/5529.bundle.js',
    '/app/distv1.8.4/5616.bundle.js',
    '/app/distv1.8.4/5692.bundle.js',
    '/app/distv1.8.4/5716.bundle.js',
    '/app/distv1.8.4/608.bundle.js',
    '/app/distv1.8.4/6161.bundle.js',
    '/app/distv1.8.4/6183.bundle.js',
    '/app/distv1.8.4/6225.bundle.js',
    '/app/distv1.8.4/6261.bundle.js',
    '/app/distv1.8.4/6402.bundle.js',
    '/app/distv1.8.4/6548.bundle.js',
    '/app/distv1.8.4/667.bundle.js',
    '/app/distv1.8.4/6675.bundle.js',
    '/app/distv1.8.4/6778.bundle.js',
    '/app/distv1.8.4/691.bundle.js',
    '/app/distv1.8.4/6984.bundle.js',
    '/app/distv1.8.4/7123.bundle.js',
    '/app/distv1.8.4/7145.bundle.js',
    '/app/distv1.8.4/7206.bundle.js',
    '/app/distv1.8.4/7214.bundle.js',
    '/app/distv1.8.4/725.bundle.js',
    '/app/distv1.8.4/7372.bundle.js',
    '/app/distv1.8.4/7428.bundle.js',
    '/app/distv1.8.4/7483.bundle.js',
    '/app/distv1.8.4/7486.bundle.js',
    '/app/distv1.8.4/7821.bundle.js',
    '/app/distv1.8.4/8348.bundle.js',
    '/app/distv1.8.4/8367.bundle.js',
    '/app/distv1.8.4/8642.bundle.js',
    '/app/distv1.8.4/8664.bundle.js',
    '/app/distv1.8.4/8706.bundle.js',
    '/app/distv1.8.4/8747.bundle.js',
    '/app/distv1.8.4/8773.bundle.js',
    '/app/distv1.8.4/8789.bundle.js',
    '/app/distv1.8.4/8797.bundle.js',
    '/app/distv1.8.4/8863.bundle.js',
    '/app/distv1.8.4/8890.bundle.js',
    '/app/distv1.8.4/9035.bundle.js',
    '/app/distv1.8.4/9071.bundle.js',
    '/app/distv1.8.4/9089.bundle.js',
    '/app/distv1.8.4/9140.bundle.js',
    '/app/distv1.8.4/9179.bundle.js',
    '/app/distv1.8.4/9649.bundle.js',
    '/app/distv1.8.4/9675.bundle.js',
    '/app/distv1.8.4/972.bundle.js',
    '/app/distv1.8.4/bundle.js'
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
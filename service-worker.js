const CACHE_NAME = 'investocc-v1.5.3';
const ASSETS_TO_CACHE = [
    '/assets/images/logo/logo-icon.png',
    '/assets/images/logo/app-icon.png',
    '/assets/images/logo/logo-icon-180.png',
    '/assets/images/icons/avatar.jpg',
    '/assets/images/my-icons/fixed-returns-tab-icon.png',
    '/assets/images/my-icons/investment.png',
    '/assets/images/my-icons/portfolio-tab-icon.png',
    '/assets/images/my-icons/stockorder.png',
    '/assets/images/my-icons/stocks-tab-icon.png',
    '/assets/images/my-icons/withdrawal.png',
    '/assets/images/crypto/bank.png',
    '/assets/images/crypto/bnb.png',
    '/assets/images/crypto/btc.png',
    '/assets/images/crypto/eth.png',
    '/assets/images/crypto/flutterwave.png',
    '/assets/images/crypto/paystack.png',
    '/assets/images/crypto/opay.png',
    '/assets/images/crypto/sol.png',
    '/assets/images/crypto/usdt.png',
    '/assets/images/icons/background.jpg',
    '/assets/images/background/bg.jpg',
    '/assets/css/distv1.5.3/style.css',
    '/assets/css/distv1.5.3/more-styles.css',
    '/assets/vendor/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css',
    '/assets/vendor/swiper/swiper-bundle.min.css',
    '/assets/js/jquery.js',
    '/assets/js/qrcode.min.js',
    '/assets/vendor/bootstrap/js/bootstrap.bundle.min.js',
    '/assets/vendor/apexcharts/dist/apexcharts.js',
    '/',
    '/index.html',
    '/distv1.5.3/1066.bundle.js',
    '/distv1.5.3/1140.bundle.js',
    '/distv1.5.3/1278.bundle.js',
    '/distv1.5.3/1283.bundle.js',
    '/distv1.5.3/1435.bundle.js',
    '/distv1.5.3/1652.bundle.js',
    '/distv1.5.3/1653.bundle.js',
    '/distv1.5.3/1684.bundle.js',
    '/distv1.5.3/1694.bundle.js',
    '/distv1.5.3/1757.bundle.js',
    '/distv1.5.3/1817.bundle.js',
    '/distv1.5.3/1956.bundle.js',
    '/distv1.5.3/2022.bundle.js',
    '/distv1.5.3/2170.bundle.js',
    '/distv1.5.3/2452.bundle.js',
    '/distv1.5.3/2528.bundle.js',
    '/distv1.5.3/2551.bundle.js',
    '/distv1.5.3/2648.bundle.js',
    '/distv1.5.3/2691.bundle.js',
    '/distv1.5.3/2718.bundle.js',
    '/distv1.5.3/3033.bundle.js',
    '/distv1.5.3/3192.bundle.js',
    '/distv1.5.3/3253.bundle.js',
    '/distv1.5.3/3284.bundle.js',
    '/distv1.5.3/3296.bundle.js',
    '/distv1.5.3/3414.bundle.js',
    '/distv1.5.3/350.bundle.js',
    '/distv1.5.3/3633.bundle.js',
    '/distv1.5.3/3706.bundle.js',
    '/distv1.5.3/3750.bundle.js',
    '/distv1.5.3/3782.bundle.js',
    '/distv1.5.3/3798.bundle.js',
    '/distv1.5.3/380.bundle.js',
    '/distv1.5.3/4186.bundle.js',
    '/distv1.5.3/4509.bundle.js',
    '/distv1.5.3/4565.bundle.js',
    '/distv1.5.3/4587.bundle.js',
    '/distv1.5.3/4815.bundle.js',
    '/distv1.5.3/4826.bundle.js',
    '/distv1.5.3/488.bundle.js',
    '/distv1.5.3/4921.bundle.js',
    '/distv1.5.3/4932.bundle.js',
    '/distv1.5.3/4977.bundle.js',
    '/distv1.5.3/5145.bundle.js',
    '/distv1.5.3/5165.bundle.js',
    '/distv1.5.3/5166.bundle.js',
    '/distv1.5.3/5289.bundle.js',
    '/distv1.5.3/5301.bundle.js',
    '/distv1.5.3/5344.bundle.js',
    '/distv1.5.3/5410.bundle.js',
    '/distv1.5.3/5482.bundle.js',
    '/distv1.5.3/5529.bundle.js',
    '/distv1.5.3/5616.bundle.js',
    '/distv1.5.3/5692.bundle.js',
    '/distv1.5.3/5716.bundle.js',
    '/distv1.5.3/608.bundle.js',
    '/distv1.5.3/6161.bundle.js',
    '/distv1.5.3/6183.bundle.js',
    '/distv1.5.3/6225.bundle.js',
    '/distv1.5.3/6261.bundle.js',
    '/distv1.5.3/6402.bundle.js',
    '/distv1.5.3/6548.bundle.js',
    '/distv1.5.3/667.bundle.js',
    '/distv1.5.3/6675.bundle.js',
    '/distv1.5.3/6778.bundle.js',
    '/distv1.5.3/691.bundle.js',
    '/distv1.5.3/6984.bundle.js',
    '/distv1.5.3/7123.bundle.js',
    '/distv1.5.3/7145.bundle.js',
    '/distv1.5.3/7206.bundle.js',
    '/distv1.5.3/7214.bundle.js',
    '/distv1.5.3/725.bundle.js',
    '/distv1.5.3/7372.bundle.js',
    '/distv1.5.3/7428.bundle.js',
    '/distv1.5.3/7483.bundle.js',
    '/distv1.5.3/7486.bundle.js',
    '/distv1.5.3/7821.bundle.js',
    '/distv1.5.3/8348.bundle.js',
    '/distv1.5.3/8367.bundle.js',
    '/distv1.5.3/8642.bundle.js',
    '/distv1.5.3/8664.bundle.js',
    '/distv1.5.3/8706.bundle.js',
    '/distv1.5.3/8747.bundle.js',
    '/distv1.5.3/8773.bundle.js',
    '/distv1.5.3/8789.bundle.js',
    '/distv1.5.3/8797.bundle.js',
    '/distv1.5.3/8863.bundle.js',
    '/distv1.5.3/8890.bundle.js',
    '/distv1.5.3/9035.bundle.js',
    '/distv1.5.3/9071.bundle.js',
    '/distv1.5.3/9089.bundle.js',
    '/distv1.5.3/9140.bundle.js',
    '/distv1.5.3/9179.bundle.js',
    '/distv1.5.3/9649.bundle.js',
    '/distv1.5.3/9675.bundle.js',
    '/distv1.5.3/972.bundle.js',
    '/distv1.5.3/bundle.js'
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
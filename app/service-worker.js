const CACHE_NAME = 'investocc-v1.8.1';
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
    '/assets/css/distv1.8.1/style.css',
    '/assets/css/distv1.8.1/more-styles.css',
    '/assets/vendor/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css',
    '/assets/vendor/swiper/swiper-bundle.min.css',
    '/assets/js/jquery.js',
    '/assets/js/qrcode.min.js',
    '/assets/vendor/bootstrap/js/bootstrap.bundle.min.js',
    '/assets/vendor/apexcharts/dist/apexcharts.js',
    '/',
    '/index.html',
    '/distv1.8.1/1066.bundle.js',
    '/distv1.8.1/1140.bundle.js',
    '/distv1.8.1/1278.bundle.js',
    '/distv1.8.1/1283.bundle.js',
    '/distv1.8.1/1435.bundle.js',
    '/distv1.8.1/1652.bundle.js',
    '/distv1.8.1/1653.bundle.js',
    '/distv1.8.1/1684.bundle.js',
    '/distv1.8.1/1694.bundle.js',
    '/distv1.8.1/1757.bundle.js',
    '/distv1.8.1/1817.bundle.js',
    '/distv1.8.1/1956.bundle.js',
    '/distv1.8.1/2022.bundle.js',
    '/distv1.8.1/2170.bundle.js',
    '/distv1.8.1/2452.bundle.js',
    '/distv1.8.1/2528.bundle.js',
    '/distv1.8.1/2551.bundle.js',
    '/distv1.8.1/2648.bundle.js',
    '/distv1.8.1/2691.bundle.js',
    '/distv1.8.1/2718.bundle.js',
    '/distv1.8.1/3033.bundle.js',
    '/distv1.8.1/3192.bundle.js',
    '/distv1.8.1/3253.bundle.js',
    '/distv1.8.1/3284.bundle.js',
    '/distv1.8.1/3296.bundle.js',
    '/distv1.8.1/3414.bundle.js',
    '/distv1.8.1/350.bundle.js',
    '/distv1.8.1/3633.bundle.js',
    '/distv1.8.1/3706.bundle.js',
    '/distv1.8.1/3750.bundle.js',
    '/distv1.8.1/3782.bundle.js',
    '/distv1.8.1/3798.bundle.js',
    '/distv1.8.1/380.bundle.js',
    '/distv1.8.1/4186.bundle.js',
    '/distv1.8.1/4509.bundle.js',
    '/distv1.8.1/4565.bundle.js',
    '/distv1.8.1/4587.bundle.js',
    '/distv1.8.1/4815.bundle.js',
    '/distv1.8.1/4826.bundle.js',
    '/distv1.8.1/488.bundle.js',
    '/distv1.8.1/4921.bundle.js',
    '/distv1.8.1/4932.bundle.js',
    '/distv1.8.1/4977.bundle.js',
    '/distv1.8.1/5145.bundle.js',
    '/distv1.8.1/5165.bundle.js',
    '/distv1.8.1/5166.bundle.js',
    '/distv1.8.1/5289.bundle.js',
    '/distv1.8.1/5301.bundle.js',
    '/distv1.8.1/5344.bundle.js',
    '/distv1.8.1/5410.bundle.js',
    '/distv1.8.1/5482.bundle.js',
    '/distv1.8.1/5529.bundle.js',
    '/distv1.8.1/5616.bundle.js',
    '/distv1.8.1/5692.bundle.js',
    '/distv1.8.1/5716.bundle.js',
    '/distv1.8.1/608.bundle.js',
    '/distv1.8.1/6161.bundle.js',
    '/distv1.8.1/6183.bundle.js',
    '/distv1.8.1/6225.bundle.js',
    '/distv1.8.1/6261.bundle.js',
    '/distv1.8.1/6402.bundle.js',
    '/distv1.8.1/6548.bundle.js',
    '/distv1.8.1/667.bundle.js',
    '/distv1.8.1/6675.bundle.js',
    '/distv1.8.1/6778.bundle.js',
    '/distv1.8.1/691.bundle.js',
    '/distv1.8.1/6984.bundle.js',
    '/distv1.8.1/7123.bundle.js',
    '/distv1.8.1/7145.bundle.js',
    '/distv1.8.1/7206.bundle.js',
    '/distv1.8.1/7214.bundle.js',
    '/distv1.8.1/725.bundle.js',
    '/distv1.8.1/7372.bundle.js',
    '/distv1.8.1/7428.bundle.js',
    '/distv1.8.1/7483.bundle.js',
    '/distv1.8.1/7486.bundle.js',
    '/distv1.8.1/7821.bundle.js',
    '/distv1.8.1/8348.bundle.js',
    '/distv1.8.1/8367.bundle.js',
    '/distv1.8.1/8642.bundle.js',
    '/distv1.8.1/8664.bundle.js',
    '/distv1.8.1/8706.bundle.js',
    '/distv1.8.1/8747.bundle.js',
    '/distv1.8.1/8773.bundle.js',
    '/distv1.8.1/8789.bundle.js',
    '/distv1.8.1/8797.bundle.js',
    '/distv1.8.1/8863.bundle.js',
    '/distv1.8.1/8890.bundle.js',
    '/distv1.8.1/9035.bundle.js',
    '/distv1.8.1/9071.bundle.js',
    '/distv1.8.1/9089.bundle.js',
    '/distv1.8.1/9140.bundle.js',
    '/distv1.8.1/9179.bundle.js',
    '/distv1.8.1/9649.bundle.js',
    '/distv1.8.1/9675.bundle.js',
    '/distv1.8.1/972.bundle.js',
    '/distv1.8.1/bundle.js'
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
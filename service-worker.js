const CACHE_NAME = 'investocc-v1.0.4';
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
    '/assets/css/distv1.0.4/style.css',
    '/assets/css/distv1.0.4/more-styles.css',
    '/assets/vendor/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css',
    '/assets/vendor/swiper/swiper-bundle.min.css',
    '/assets/js/jquery.js',
    '/assets/js/qrcode.min.js',
    '/assets/vendor/bootstrap/js/bootstrap.bundle.min.js',
    '/assets/vendor/apexcharts/dist/apexcharts.js',
    '/',
    '/index.html',
    '/distv1.0.4/1066.bundle.js',
    '/distv1.0.4/1140.bundle.js',
    '/distv1.0.4/1278.bundle.js',
    '/distv1.0.4/1283.bundle.js',
    '/distv1.0.4/1435.bundle.js',
    '/distv1.0.4/1652.bundle.js',
    '/distv1.0.4/1653.bundle.js',
    '/distv1.0.4/1684.bundle.js',
    '/distv1.0.4/1694.bundle.js',
    '/distv1.0.4/1757.bundle.js',
    '/distv1.0.4/1817.bundle.js',
    '/distv1.0.4/1837.bundle.js',
    '/distv1.0.4/1956.bundle.js',
    '/distv1.0.4/2022.bundle.js',
    '/distv1.0.4/2170.bundle.js',
    '/distv1.0.4/2528.bundle.js',
    '/distv1.0.4/2551.bundle.js',
    '/distv1.0.4/2648.bundle.js',
    '/distv1.0.4/2691.bundle.js',
    '/distv1.0.4/2718.bundle.js',
    '/distv1.0.4/3033.bundle.js',
    '/distv1.0.4/3192.bundle.js',
    '/distv1.0.4/3253.bundle.js',
    '/distv1.0.4/3284.bundle.js',
    '/distv1.0.4/3296.bundle.js',
    '/distv1.0.4/3414.bundle.js',
    '/distv1.0.4/350.bundle.js',
    '/distv1.0.4/3633.bundle.js',
    '/distv1.0.4/3706.bundle.js',
    '/distv1.0.4/3750.bundle.js',
    '/distv1.0.4/3782.bundle.js',
    '/distv1.0.4/3798.bundle.js',
    '/distv1.0.4/380.bundle.js',
    '/distv1.0.4/4186.bundle.js',
    '/distv1.0.4/4509.bundle.js',
    '/distv1.0.4/4587.bundle.js',
    '/distv1.0.4/4815.bundle.js',
    '/distv1.0.4/4826.bundle.js',
    '/distv1.0.4/488.bundle.js',
    '/distv1.0.4/4921.bundle.js',
    '/distv1.0.4/4932.bundle.js',
    '/distv1.0.4/4977.bundle.js',
    '/distv1.0.4/5145.bundle.js',
    '/distv1.0.4/5165.bundle.js',
    '/distv1.0.4/5166.bundle.js',
    '/distv1.0.4/5289.bundle.js',
    '/distv1.0.4/5301.bundle.js',
    '/distv1.0.4/5344.bundle.js',
    '/distv1.0.4/5410.bundle.js',
    '/distv1.0.4/5482.bundle.js',
    '/distv1.0.4/5529.bundle.js',
    '/distv1.0.4/5616.bundle.js',
    '/distv1.0.4/5692.bundle.js',
    '/distv1.0.4/5716.bundle.js',
    '/distv1.0.4/608.bundle.js',
    '/distv1.0.4/6161.bundle.js',
    '/distv1.0.4/6183.bundle.js',
    '/distv1.0.4/6225.bundle.js',
    '/distv1.0.4/6261.bundle.js',
    '/distv1.0.4/6318.bundle.js',
    '/distv1.0.4/6402.bundle.js',
    '/distv1.0.4/6548.bundle.js',
    '/distv1.0.4/667.bundle.js',
    '/distv1.0.4/6675.bundle.js',
    '/distv1.0.4/6778.bundle.js',
    '/distv1.0.4/691.bundle.js',
    '/distv1.0.4/6984.bundle.js',
    '/distv1.0.4/7123.bundle.js',
    '/distv1.0.4/7145.bundle.js',
    '/distv1.0.4/7206.bundle.js',
    '/distv1.0.4/7214.bundle.js',
    '/distv1.0.4/725.bundle.js',
    '/distv1.0.4/7292.bundle.js',
    '/distv1.0.4/7372.bundle.js',
    '/distv1.0.4/7428.bundle.js',
    '/distv1.0.4/7483.bundle.js',
    '/distv1.0.4/7486.bundle.js',
    '/distv1.0.4/7821.bundle.js',
    '/distv1.0.4/8348.bundle.js',
    '/distv1.0.4/8367.bundle.js',
    '/distv1.0.4/8642.bundle.js',
    '/distv1.0.4/8664.bundle.js',
    '/distv1.0.4/8706.bundle.js',
    '/distv1.0.4/8747.bundle.js',
    '/distv1.0.4/8773.bundle.js',
    '/distv1.0.4/8789.bundle.js',
    '/distv1.0.4/8863.bundle.js',
    '/distv1.0.4/8890.bundle.js',
    '/distv1.0.4/9035.bundle.js',
    '/distv1.0.4/9071.bundle.js',
    '/distv1.0.4/9089.bundle.js',
    '/distv1.0.4/9140.bundle.js',
    '/distv1.0.4/9179.bundle.js',
    '/distv1.0.4/9314.bundle.js',
    '/distv1.0.4/9649.bundle.js',
    '/distv1.0.4/9675.bundle.js',
    '/distv1.0.4/972.bundle.js',
    '/distv1.0.4/bundle.js'
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
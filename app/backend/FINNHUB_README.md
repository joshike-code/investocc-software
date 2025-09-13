# Finnhub Integration Documentation

## Overview

This integration provides real-time stock price data using the Finnhub.io API. The system combines static stock metadata stored in your database with live price data from Finnhub, providing the best of both worlds.

## Features

- **Real-time Price Updates**: Get current stock prices, daily changes, and market data
- **Market Status Detection**: Real-time US market open/closed status with timezone handling
- **Smart Caching**: 1-hour cache to reduce API calls and improve performance
- **Rate Limiting**: Built-in delays to respect Finnhub's free tier limits (60 calls/minute)
- **Fallback System**: Uses database prices if API is unavailable
- **Automated Updates**: Cron job for daily price synchronization
- **Admin Controls**: Manual price update endpoints for administrators

## Setup

### 1. API Key Configuration

Add your Finnhub API key to the `.env` file:
```
FINNHUB_API_KEY=your_api_key_here
```

### 3. Set Up Automated Updates (Optional)

#### For cPanel/Shared Hosting:
1. Go to your cPanel → Cron Jobs
2. Add a new cron job with this command:
```bash
/usr/bin/php /path/to/your/site/investocc-backend/cron/update_stocks.php
```
3. Set schedule: `0 9 * * 1-5` (9 AM, Monday-Friday)

#### For VPS/Dedicated Server:
```bash
# Edit crontab
crontab -e

# Add this line
0 9 * * 1-5 /usr/bin/php /path/to/your/site/investocc-backend/cron/update_stocks.php
```

#### For Windows Task Scheduler:
1. Open Task Scheduler
2. Create Basic Task
3. Set trigger: Daily at 9 AM
4. Action: Start a program
5. Program: `php.exe`
6. Arguments: `C:\xampp\htdocs\investocc-backend\cron\update_stocks.php`

## API Endpoints

### Get All Stocks (with live data + market status)
```
GET /api/stocks
```
Returns all stocks with live price data from Finnhub plus current market status.

**Response includes:**
- `categories`: Array of stock categories
- `stocks`: Array of stocks with live prices
- `market_status`: Real-time market open/closed status

### Get Stock by Category (with live data)
```
GET /api/stocks?filter=technology
```

### Get Single Stock (with live data)
```
GET /api/stocks?id=123
```

### Search Stocks (with live data)
```
GET /api/stocks?search=apple
```

### Manual Price Update (Admin only)
```
POST /api/stocks?action=update_prices
Authorization: Bearer <admin_token>
```

## How It Works

### Data Flow
1. **Static Data**: Stock metadata (name, description, categories) stored in database
2. **Live Data**: Current prices, changes, and market data from Finnhub API
3. **Caching**: Live data cached for 5 minutes to reduce API calls
4. **Fallback**: If API fails, uses database prices

### Rate Limiting
- Free tier: 60 calls per minute
- Implementation includes 1.1-second delays between calls
- Caching reduces actual API calls significantly

### Error Handling
- API failures are logged but don't break the application
- Graceful fallback to database prices
- Detailed logging for troubleshooting

## Finnhub API Response Format

```json
{
  "c": 150.25,    // Current price
  "h": 152.00,    // High price of the day
  "l": 149.50,    // Low price of the day
  "o": 151.00,    // Open price of the day
  "pc": 151.20,   // Previous close price
  "t": 1234567890 // Timestamp
}
```

## Database Integration

The system updates these fields in your `stocks` table:
- `price` - Current stock price
- `today_percent` - Daily percentage change
- `today_p_l` - Daily profit/loss in dollars
- `open` - Opening price
- `high` - Day's high price
- `low` - Day's low price
- `last_update` - Timestamp of last update

## Caching System

Cache files are stored in `/cache/` directory:
- Format: `stock_SYMBOL.json`
- TTL: 1 hour (configurable)
- Automatic cleanup of old cache files

## Monitoring

### Log Files
- `update_stock_prices.log` - Cron job execution logs
- `error/server_errors.log` - API and system errors

### Cache Directory
Check `/cache/` for recent stock data files to verify API calls are working.

## Market Status Feature

### Market Hours Detection
The system automatically detects US stock market hours (NYSE/NASDAQ):
- **Regular Hours**: Monday-Friday, 9:30 AM - 4:00 PM ET
- **Weekends**: Automatically detected as closed
- **Holidays**: Major US market holidays are recognized
- **Timezone**: All calculations in Eastern Time (America/New_York)

### Market Status Response
```json
{
  "is_open": true,
  "status": "open",
  "reason": "Regular Trading Hours",
  "closes_at": "2025-09-11 16:00:00 EDT",
  "time_until_close": "03:35:20",
  "timezone": "America/New_York",
  "current_time": "2025-09-11 12:24:40 EDT"
}
```

### Supported Holidays
- New Year's Day
- Martin Luther King Jr. Day
- Presidents Day
- Good Friday
- Memorial Day
- Juneteenth
- Independence Day
- Labor Day
- Thanksgiving Day
- Christmas Day

### Frontend Integration
```javascript
// Check if market is open
if (data.market_status.is_open) {
  console.log(`Market closes in ${data.market_status.time_until_close}`);
} else {
  console.log(`Market closed: ${data.market_status.reason}`);
  console.log(`Next open: ${data.market_status.next_open}`);
}
```

## Troubleshooting

### Common Issues

1. **API Key Invalid**
   - Verify key in `.env` file
   - Check Finnhub dashboard for key status

2. **Rate Limiting**
   - Free tier: 60 calls/minute
   - Consider upgrading for higher limits

3. **Cache Permission Issues**
   - Ensure `/cache/` directory is writable
   - Check PHP error logs

4. **Cron Job Not Running**
   - Verify cron syntax and PHP path
   - Check cron logs: `grep CRON /var/log/syslog`

### Testing Individual Components

```bash
# Test API connection
php -r "require 'services/FinnhubService.php'; var_dump(FinnhubService::getQuote('AAPL'));"

# Test database update
php -r "require 'services/FinnhubService.php'; var_dump(FinnhubService::updateStockPrices(['AAPL']));"

# Run cron job manually
php cron/update_stocks.php
```

## Performance Considerations

### API Usage Optimization
- 1-hour caching reduces API calls by ~98%
- Smart updates only fetch stale data
- Batch updates during off-hours
- Consider upgrading Finnhub plan for real-time needs

### Database Performance
- Indexes on `trade_name` for fast lookups
- Consider read replicas for high-traffic sites

## Security

- API key stored in environment variables
- Admin-only manual update endpoints
- Input sanitization on all parameters
- Rate limiting prevents API abuse

## Cost Management

### Finnhub Free Tier
- 60 calls/minute
- ~100 stocks = ~2 minutes to update all
- Daily updates well within limits

### Upgrade Considerations
- Real-time data requires paid plan
- Higher rate limits for larger portfolios
- WebSocket support for live updates

## Support

For issues with this integration:
1. Check the logs first
2. Run the test script
3. Verify API key and network connectivity
4. Check Finnhub service status

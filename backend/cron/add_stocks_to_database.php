<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/StockService.php';

function allStocks(): array {
    return array (
        array ( 
            'name' => 'Apple, Inc.',
            'trade_name' => 'AAPL',
            'price' => '200.49',
            'description' => "Apple is one of the world's largest companies. Its most well-known products are the iphone, ipod and MAC computer. Apple is currently the world's most valuable brand.",
            'about' => "Apple is among the largest companies in the world, with a broad protfolio of hardware and software products targeted at consumers and businesses. Apple's iphone makes up a majority of the firm sales, and Apple's other products like Max, iPad, and Watch are designed around the iPhone as the focal point of an expansive software ecosystem.",
            'sector' => "Electronic Computers",
            'categories' => ['featured', 'moneystart', 'magnificient-7', 'warren-buffet', 'technology', 'most-popular', 'all-stocks', 'ross-gerber', 'most-bought', 'biggest-companies', 'investing-starterpack', 'self-driving', 'dividend-stocks', 'ken-griffin']
        ),
        array ( 
            'name' => 'Amazon.com Inc.',
            'trade_name' => 'AMZN',
            'price' => '205.00',
            'description' => "Amazon is the largest internet-based retailer in the United States. The Company sells nearly every item under the sun, either directly or through third-party merchants. Amazon also produces consumer electronics and provides cloud computing services.",
            'about' => "Amazon is the leading online retailer and marketplace for third party sellers. Retail related revenue represents approximately 75% of total, followed by Amazon Web Services' cloud computing, storage, database, and other offerings (15%), advertising services (5% to 10%), and other the remainder. International segments constitute",
            'sector' => "Retail-catalog & Mail-order houses",
            'categories' => ['featured', 'moneystart', 'magnificient-7', 'technology', 'cheap-to-buy', 'motorsport', 'most-popular', 'all-stocks', 'rory', 'ross-gerber', 'most-bought', 'biggest-companies', 'investing-starterpack', 'ken-griffin']
        ),
        array ( 
            'name' => 'Global X MSCI Argentina ETF',
            'trade_name' => 'ARGT',
            'price' => '90.79',
            'description' => "The Fund seeks to provide investment results that corresponding generally to the price and yield performance of the Argentina Index.",
            'about' => "",
            'sector' => "",
            'categories' => ['featured', 'best-performing-2024']
        ),
        array ( 
            'name' => 'The Walt Disney Company',
            'trade_name' => 'DIS',
            'price' => '112.78',
            'description' => "Founded in 1923 by Walt Disney, this entertainment giant owns media networks such as ABC; resorts and studios such as Marvel Studios.",
            'about' => "Disney operates in three global business segments: entertainment, sports, and experiences. Entertainment, sports, and experiences. Entertainment and experiences both benefit from the firm's ownership of iconic franchises and characters.",
            'sector' => "Services-Miscellaneous Amusement parks",
            'categories' => ['featured', 'entertainment', 'all-stocks', 'ross-gerber', 'emmet-savage']
        ),
        array ( 
            'name' => 'Energy Transfer Equity, L.P.',
            'trade_name' => 'ET',
            'price' => '17.38',
            'description' => "Energy Transfer Equity, L.P. is the former name of Energy Transfer LP, a master limited partnership. Its only assets is a 100% interest in Energy Transfer Operating L.P.",
            'about' => "Energy Transfer owns one of the largest portfolios of crude oil, natural gas, and natural gas liquid assets in the US, primarily in Texas and the US midcontinent region. Its pipeline network includes more than 12,000 miles of iterstate pipelines. It also owns gathering, processing and storage facilities.",
            'sector' => "Natural Gas Transmission",
            'categories' => ['featured']
        ),
        array ( 
            'name' => 'Grayscale Bitcoin Trust ETF',
            'trade_name' => 'GBTC',
            'price' => '83.00',
            'description' => "Grayscale Bitcoin Trust (BTC) engages in the holding of Bitcoin and issuance of common units of fractional undivided beneficial interest in exchange for bitcoin. The company was founded on September 13, 2013 and is headquartered in Stamford, CT.",
            'about' => "Energy Transfer owns one of the largest portfolios of crude oil, natural gas, and natural gas liquid assets in the US, primarily in Texas and the US midcontinent region. Its pipeline network includes more than 12,000 miles of iterstate pipelines. It also owns gathering, processing and storage facilities.",
            'sector' => "Commodity contracts Brokers",
            'categories' => ['featured', 'bitcoin-etfs']
        ),
        array ( 
            'name' => 'Alphabet Inc. - Class A Shares',
            'trade_name' => 'GOOGL',
            'price' => '169.05',
            'description' => "Google specializes in internet-related servoces including online advertising technologies, search functionality, cloud computing and software. Google also owns youtube.com",
            'about' => "Alphabet is a holding company that wholly owns internet giant Google. The California-based company derives slightly less than 90% of its revenue form Google services, he vast majority of which is advertising sales",
            'sector' => "Services-Computer Progarmming",
            'categories' => ['featured', 'technology', 'cheap-to-buy', 'moneystart', 'investing-starterpack', 'all-stocks', 'ai-2025', 'magnificient-7', 'biggest-companies', 'ken-griffin', 'bill-gate']
        ),
        array ( 
            'name' => 'Microsoft Corporation',
            'trade_name' => 'MSFT',
            'price' => '465.04',
            'description' => "Microsoft Corporation is a software and computer company best known for its Windows operating systems and Office Suite software. It is one of the world's most valuable companies by market cap.",
            'about' => "Microsoft develops and licenses consumer and enterprise software. It is known for its windows operating systems and Office productivity suite. The company is organized into three equally sized broad segments: productivity and business processes.",
            'sector' => "Services-Prepackaged Software",
            'categories' => ['featured', 'technology', 'cheap-to-buy', 'moneystart', 'motorsport', 'investing-starterpack', 'all-stocks', 'ai-2025', 'magnificient-7', 'most-popular', 'ken-griffin', 'bill-gate', 'ross-gerber']
        ),
        array ( 
            'name' => 'Netflix, Inc.',
            'trade_name' => 'NFLX',
            'price' => '1219.54',
            'description' => "Netflix provides on-demand streaming of movies and television shows to subscribers worldwide. They also have a DVD-by-mail services that offers an expanded selection of titles.",
            'about' => "Netflix's relatively simple business model involves only one business, its streaming service. It has ths biggest television entertainment subscriber base in both the United States and the collective international market, with more than 300 million subscribers globally.",
            'sector' => "Services-Video Tape Rental",
            'categories' => ['featured', 'technology', 'entertainment', 'all-stocks', 'emmet-savage', 'most-popular', 'ken-griffin']
        ),
        array ( 
            'name' => 'NextTracker Inc.',
            'trade_name' => 'NXT',
            'price' => '55.26',
            'description' => "Nextracker, Inc. engages in the provision of integrated and solar tracker and software solutions used in utility-scale and ground-mounted distributed generation solar projects.",
            'about' => "Nextracker (and its subsidiaries) is a leading provider of intelligent, integrated solar tracker and software solutions used in utility-scale and distributed generation solar projects around the world. Nextracker's products enable solar panels in utility-scale power plans to follow the sun's movement across the sky and optimize plant",
            'sector' => "Search, Detection, Navigation",
            'categories' => ['featured', 'energy-stocks']
        ),
    );
}

$stocks = allStocks();

$conn = Database::getConnection();
$query = "INSERT INTO stocks (name, trade_name, price, description, about, sector, categories)
          VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);

if (!$stmt) {
    die("❌ Prepare failed: " . $conn->error);
}

foreach ($stocks as $stock) {
    $name = $stock['name'];
    $trade_name = $stock['trade_name'];
    $price = $stock['price'];
    $description = $stock['description'];
    $about = $stock['about'];
    $sector = $stock['sector'];
    $categories = json_encode($stock['categories']);

    $stmt->bind_param(
        "ssdssss",
        $name,
        $trade_name,
        $price,
        $description,
        $about,
        $sector,
        $categories
    );

    if ($stmt->execute()) {
        echo "✅ Inserted: $name\n";
    } else {
        echo "❌ Failed to insert $name: " . $stmt->error . "\n";
    }
}

$stmt->close();
$conn->close();
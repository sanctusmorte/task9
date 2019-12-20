<?


require_once __DIR__ . '/vendor/autoload.php';


use Devristo\Phpws\Server\WebSocketServer;


class WsBitcointBot
{
    /**
     * Connect to mainnet.infura.io using WS and getting new transactions to all wallets from DB
     *  
     * After each message from connecting WS insert new transaction to DB
     *  
     * @return void
     */
    public function connectToEthApi()
    {
        $loop = \React\EventLoop\Factory::create();
        $logger = new \Zend\Log\Logger();
        $writer = new Zend\Log\Writer\Stream("php://output");
        $logger->addWriter($writer);
        $client = new \Devristo\Phpws\Client\WebSocket("wss://mainnet.infura.io/ws", $loop, $logger);
        $client->on("request", function($headers) use ($logger){
            $logger->notice("Request object created!");
        });
        $client->on("handshake", function() use ($logger) {
            $logger->notice("Handshake received!");
        });
        $client->on("connect", function() use ($logger, $client){
            // get Wallets from DB
            $wallets = $this->getWallets();
            $wallets = json_encode($wallets);
            $logger->notice("Connected!");
            $json = '{"jsonrpc":"2.0", "id": 1, "method": "eth_subscribe", "params": ["logs", {"address": '.$wallets.'}]}';
            $client->send($json);
        });
        $client->on("message", function($message) use ($client, $logger){
            // get message from ws
            $result = $message->getData();
            $result = json_decode($result, true);
            if (isset($result['params']) == true) {
                $params = $result['params']['result'];
                $pdo = $this->dbConnect();
                try {
                    $added_transaction = $pdo->query('INSERT INTO transactions SET wallet = "'.$params["address"].'", id_transaction = "'.hexdec($params["transactionIndex"]).'", confirmations = "'.hexdec($params["blockNumber"]).'", date_added = "'.time().'" ');
                    if ($added_transaction !== false) {
                        $logger->notice("Транзакция: " . hexdec($params["transactionIndex"]) . " успешно добавлена!");
                    }
                } catch (PDOException $e) {
                    die('Подключение к БД при добавлении транзакции не удалось');
                }   
            }          
        });
        $client->open();
        $loop->run();
    }
    /**
     * PDO connection to DB
     *
     * @return class $pdo
     */
    public function dbConnect()
    {
        $host = '127.0.0.1';
        $db   = 'nygma_test';
        $user = 'mysql';
        $password = 'mysql';
        $charset = 'utf8';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, $user, $password);
        } catch (PDOException $e) {
            die('Подключение к БД не удалось');
        }
        return $pdo;        
    }
    /**
     * Get all wallets from DB
     *
     * @return array $wallets - list of wallets
     */
    public function getWallets()
    {
        $pdo = $this->dbConnect();
        $wallets = [];
        try {
            $results = $pdo->query('SELECT * FROM wallets');
        } catch (PDOException $e) {
            die('Получить кошельки из БД не удалось');
        }
        while ($row = $results->fetch())
        {
            $wallets[] = $row['wallet'];
        }       
        return $wallets;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Wallet;



class WalletsController extends Controller
{
    /**
     * Display the table of wallets
     *
     * @return view
     */
     
    public function index()
    {
        $wallets = $this->getWallets();
        
        return view('wallets', compact('wallets'));
    }

    /**
     * Get all wallets from DB
     *
     * @return array $wallets - list of wallets
     */

    public function getWallets()
    {
        // update balance of all wallets
        $this->updateBalanceOfWallets();        
        
        $wallets = Wallet::orderBy('id', 'DESC')->get();

        return $wallets;
    }
    
    /**
     * Update balance of all wallets if have passed more than 5 minutes after last update
     * 
     * @return void
     */
     
    public function updateBalanceOfWallets()
    {
        $wallets = Wallet::all();
        
        $now = time();
        
        foreach ($wallets as $wallet) {
            
            // Update balance of all wallets if have passed more than 5 minutes after last update
            if ($now - $wallet['update_balance_date'] > 300) {
                
                $balance = $this->getBalanceOfWallet($wallet['wallet']);
                
                $edited_wallet = Wallet::where('wallet', $wallet['wallet'])->update(['balance' => hexdec($balance['result']), 'update_balance_date' => $now]);
                
            }
        }
    }
    
    /**
     * Add wallet in DB
     * 
     * @param array $request - data from page via ajax
     * 
     * @return array json_response to page
     */
    
    public function addWallet(Request $request)
    {
        $wallet_address = trim($request->get('wallet'));
        
        // get balance of wallet in wei
        $balance = $this->getBalanceOfWallet($wallet_address);

        // check if wallet is exists in Ethereum
        $json_response = [];
        if (isset($balance['error']) == true) {
            $json_response['is_exists_in_eth'] = 0;
        } else {
            
            // check if wallet is exists in DB
            $wallet = Wallet::where('wallet', $wallet_address)->first();

            if ($wallet == null) {
                $json_response['is_exists_in_db'] = 0;
                
                $wallet_request = new Wallet;
                
                $wallet_request->wallet                  = $wallet_address;
                $wallet_request->balance                 = hexdec($balance['result']);
                $wallet_request->added_date              = time();
                $wallet_request->update_balance_date     = time();
                
                $wallet_request->save();
                 
            } else {
                $json_response['is_exists_in_db'] = 1;
            }
            
            $json_response['is_exists_in_eth'] = 1;
        }

        return response()->json([ 'response' => $json_response ]);
    }    
    
    /**
     * delete wallet
     * 
     * @param array $request - data from page via ajax
     * 
     * @return array json_response to page
     */
     
    public function deleteWallet(Request $request)
    {
        $wallet_address = trim($request->get('wallet'));
        
        
        $deleted = Wallet::where('wallet', $wallet_address)->delete();  
        
        if ($deleted == 1) {
            $json_response['is_delete_ok'] = 1;    
        } else {
            $json_response['is_delete_ok'] = 0; 
        }
        
        return response()->json([ 'response' => $json_response ]);
    }
    
    /**
     * Get balance of wallet in wei using Ethereum API from infura.io service via POST request using curl
     * 
     * @param string $wallet_address
     * 
     * $return array $balance - response from Ethereum API infura.io
     */
    
    public function getBalanceOfWallet($wallet_address)
    {
        // curl setting
        $data = [];
        $data['method'] = 'eth_getBalance';
        $data['params'] = [$wallet_address, "latest"];       
        $data['id'] = 1;
        
        $balance = $this->curlPost($data);
        
        return $balance;
    }
    
    /**
     * POST request using Curl
     * 
     * @param array $data - settings for curl
     * 
     * @return array $result - result of POST request
     */

    public function curlPost($data)
    {
        $curl_url = 'https://mainnet.infura.io/v3/4cbdf29ccd06462d8ec00eee0842529b';
        
        // jsonrpc settings for curl
        $request = [];
        $request['jsonrpc'] = '2.0';
        $request['method'] = $data['method'];
        $request['params'] = $data['params'];
        $request['id'] = $data['id'];
        $request = json_encode($request);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curl_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        $result = curl_exec($ch);
        curl_close($ch);         
        
        return json_decode($result, 1);    
    }

}

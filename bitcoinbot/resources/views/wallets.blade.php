<!DOCTYPE html>
<html>
    <head>
        
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        
        <title>Wallets</title>
        
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        
        
        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
        
        <style>
            body {
                margin: 0;
                padding: 0;
                width: 100%;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }       
            
            .table {
                margin:0 auto;
                padding-top: 100px;
            }
            
            .add_wallet {
                padding-top: 50px;
                text-align: center;
            }
            
            .add_wallet input {
                width: 400px;
                height: 50px;
                border:1px solid black;
                padding-left: 15px;
            }
            
            .add_wallet button {
                cursor: pointer;
                margin-left: 20px;
                width: 150px;
                height: 50px;
                border-radius: 50px;
                border: 0;
                color: #fff;
                background: #32383e;
            }
            
            .delete_wallet {
                text-decoration: underline;
                cursor: pointer;
            }
            
            .add_wallet_error {
            	color: red;
            	font-size: 20px;
            	font-weight: bold;
            	padding-top: 10px;
            }
            
            .add_wallet_info {
            	color: green;
            	font-size: 20px;
            	font-weight: bold;
            	padding-top: 10px;                
            }
            
        </style>
        
    </head>
    
    <body>
        <table class="table table-dark">
          <thead>
            <tr>
              <th scope="col">id</th>
              <th scope="col">Wallet</th>
              <th scope="col">Balance (in Wei)</th>
              <th scope="col">Action</th>
            </tr>
          </thead>
          <tbody>
            <?
                foreach ($wallets as $wallet) { ?>
                    <tr>
                      <th scope="row"><? echo $wallet['id']; ?></th>
                      <td id="wallet_address"><? echo $wallet['wallet']; ?></td>
                      <td><? echo $wallet['balance'] !== '' ? $wallet['balance'] : '0' ; ?></td>
                      <td><span onclick="deleteWallet(this)" class="delete_wallet">Delete</span></td>
                    </tr>                     
                <? }
            ?>

          </tbody>
        </table>
        
        <div class="add_wallet">
            <input id="wallet_address" value="" minlength="40" autocomplete="off" placeholder="Wallet address" type="text">
            <button onclick="addWallet(this);">Add</button>
            <div class="add_wallet_error"></div>
            <div class="add_wallet_info"></div>
        </div>
        
        
        <script
			  src="https://code.jquery.com/jquery-2.2.4.min.js"
			  integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
			  crossorigin="anonymous"></script>
        </script>
            
            
        <script>
            function addWallet(that)
            {
                let wallet = $(that).siblings('#wallet_address').val();
                let wallet_length = wallet.length;
                
                if (wallet_length < 40 || wallet_length > 44) {
                    let error = 'Ethereum wallet must be between 40 and 44 characters long!';
                    $(that).siblings('.add_wallet_error').text(error);
                    return false;
                }
                
                
                $.ajax({
                   headers: {
                       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                   },               
                   type:'POST',
                   url:'/add-wallet',
                   data: {
                        wallet : wallet
                   },
                   success:function(data) {
                      if (data.response.is_exists_in_eth == 0) {
                          let error = 'wallet does not exist on Ethereum!'
                          $('.add_wallet_error').text(error);
                      } else if (data.response.is_exists_in_db == 1) {
                          let error = 'wallet is already exist in DB!'
                          $('.add_wallet_error').text(error);                          
                      } else if (data.response.is_exists_in_db == 0) {
                          let info = 'wallet successfully added!'
                          $('.add_wallet_info').text(info);
                          
                           setTimeout(function(){
                              location.reload();
                           }, 3000);

                      }
                   }
                });                
            }
            
            function deleteWallet(that)
            {
                let wallet = $(that).parent().siblings('#wallet_address').text();
                
                $.ajax({
                   headers: {
                       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                   },               
                   type:'POST',
                   url:'/delete-wallet',
                   data: {
                        wallet : wallet
                   },
                   success:function(data) {
                    if (data.response.is_delete_ok == 1) {
                        let info = 'wallet successfully deleted!';
                        alert(info);
                           setTimeout(function(){
                              location.reload();
                           }, 3000);                        
                    } else {
                        let info = 'something problem with delete of wallet!';
                        alert(info);   
                           setTimeout(function(){
                              location.reload();
                           }, 3000);                        
                    }
                    
                    
                   }
                });                
            }            
            
        </script>
        
    </body>
</html>


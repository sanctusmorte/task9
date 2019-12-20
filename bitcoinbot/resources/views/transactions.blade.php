<!DOCTYPE html>
<html>
    <head>
        
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        
        <title>Realtime Transactions</title>
        
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
              <th scope="col">Wallet</th>
              <th scope="col">Id of transaction</th>
              <th scope="col">confirmations</th>
            </tr>
          </thead>
          <tbody id="transactions_list">
            <?
                foreach ($transactions as $transaction) { ?>
                    <tr>
                        <td><? echo $transaction['wallet']; ?></td>
                        <td><? echo $transaction['id_transaction']; ?></td>
                        <td><? echo $transaction['confirmations']; ?></td>
                    </tr>                     
                <? }
            ?>
          </tbody>
        </table>
        
        
        <script
			  src="https://code.jquery.com/jquery-2.2.4.min.js"
			  integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
			  crossorigin="anonymous"></script>
        </script>
        
        <script>
            function getTransactionsEach10secFromPage()
            {
                    $.ajax({
                        headers: {
                           'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },               
                        type:'POST',
                        url:'/getTransactionsEach10secFromPage',
                        data: {},
                        success:function(data) {
                            $.each(JSON.parse(data), function(key, item){
                                if ($('#transactions_list tr').length == 15) {
                                    $('#transactions_list tr:last-child').remove()
                                } 
                                $('<tr><td>'+item.wallet+'</td><td>'+item.id_transaction+'</td><td>'+item.confirmations+'</td></tr>').insertBefore('#transactions_list tr:nth-child(1)');                               
                            });
                       }
                    });
            setTimeout(getTransactionsEach10secFromPage, 10000);
            }

            setTimeout(getTransactionsEach10secFromPage, 1000);
        </script>        
        
    </body>
</html>
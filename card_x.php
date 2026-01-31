<html>
<head>
<title>Simple iFrame API Example</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<script src="backend_assets/node_modules/jquery/jquery-3.2.1.min.js"></script>
<script language="javascript" src="https://pay1.plugnpay.com/api/iframe/0fSnYpPTlu/client/"></script>
<script language="javascript">
$(document).ready(function() {
  payment_api.setCallback(function(data) {

      // 'data' is the querystring returned by the payment request.
      // Perform any response handling you would like to do here.
      // For example, such as putting the value of data into a field
      //   and calling submit on the form.
      alert(data);

  })
});
</script>
</head>

<body>
<u>Simple iFrame API Example</u>
<br>(View Source Code Of Web Page For Details)

<form>
<input type="hidden" id="publisher_name" value="diamondir1">
<input type="hidden" id="mode" value="auth">
<input type="hidden" id="convert" value="underscores">

<br>Amount: $<input type="text" id="card_amount">
<br>Name: <input type="text" id="card_name">
<br>Card Number: <input type="text" id="card_number">
<br>Exp Date: <input type="text" id="card_exp"> [MM/YY]
<br>CVV: <input type="text" id="card_cvv">

<br><input type="button" value="Submit" onClick="payment_api.send();">
</form>

</body>
</html>


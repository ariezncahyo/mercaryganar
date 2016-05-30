<?php
if(!isset($_SESSION)){
    session_start();
}
include_once("z_db.php");
//session_start();



$sql="SELECT maintain FROM  settings WHERE sno=0";
		  if ($result = mysqli_query($con, $sql)) {

    /* fetch associative array */
    while ($row = mysqli_fetch_row($result)) {
        $main= $row[0];
    }
	if($main==2 || $main==3)
	{
	print "
				<script language='javascript'>
					window.location = 'maintain.php';
				</script>
			";
	}

}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username']) && isset($_POST['todo']))
{
// Collect the data from post method of form submission // 
$todo=mysqli_real_escape_string($con,$_POST['todo']);
$name=mysqli_real_escape_string($con,$_POST['fname']);
$username=mysqli_real_escape_string($con,$_POST['username']);
$userid=mysqli_real_escape_string($con,$_POST['username']);
$password=mysqli_real_escape_string($con,$_POST['password']);
$password2=mysqli_real_escape_string($con,$_POST['password2']);

$email=mysqli_real_escape_string($con,$_POST['email']);

$mobile=mysqli_real_escape_string($con,$_POST['mobile']);
$ref=mysqli_real_escape_string($con,$_POST['referral']);
$address=mysqli_real_escape_string($con,$_POST['address']);
$country=mysqli_real_escape_string($con,$_POST['country']);
$package=mysqli_real_escape_string($con,$_POST['package']);

$status = "OK";
$msg="";
//validation starts
// if userid is less than 6 char then status is not ok
if(!isset($username) or strlen($username) <6){
$msg=$msg."El nombre de usuario debe contener 6 caracteres minimo.<BR>";
$status= "NOTOK";}					

if(!ctype_alnum($username)){
$msg=$msg."El nombre de usuario solo debe contener alfanumericos.<BR>";
$status= "NOTOK";}					


$rr=mysqli_query($con,"SELECT COUNT(*) FROM affiliateuser WHERE username = '$username'");
$r = mysqli_fetch_row($rr);
$nr = $r[0];
if($nr==1){
$msg=$msg."Este nombre de usuario ya existe!, Intenta con otro.<BR>";
$status= "NOTOK";
}	

$rrr=mysqli_query($con,"SELECT COUNT(*) FROM affiliateuser WHERE mobile = '$mobile'");
$r3 = mysqli_fetch_row($rrr);
$nr3 = $r3[0];
if($nr3==1){
$msg=$msg."Este numero celular ya esta registrado.<BR>";
$status= "NOTOK";
}	

$remail=mysqli_query($con,"SELECT COUNT(*) FROM affiliateuser WHERE email = '$email'");
$re = mysqli_fetch_row($remail);
$nremail = $re[0];
if($nremail==1){
$msg=$msg."El correo electronico ya esta registrado.<BR>";
$status= "NOTOK";
}				

$result = mysqli_query($con,"SELECT count(*) FROM  affiliateuser where username = '$ref'");
$row = mysqli_fetch_row($result);
$numrows = $row[0];
if ($numrows==0)
{
$msg=$msg."El nombre de tu referido no existe..<BR>";
$status= "NOTOK";
}

if ( $package=="" ){
$msg=$msg."Por favor selecciona un paquete.<BR>";
$status= "NOTOK";}	


if ( strlen($password) < 8 ){
$msg=$msg."La contraseña debe tener mas de 8 caracteres.<BR>";
$status= "NOTOK";}	

if ( strlen($address) < 1 ){
$msg=$msg."No disponible<BR>";
}

if ( strlen($mobile) > 15 ){
$msg=$msg."Ingrese un numero de telefono valido<BR>";
}

if ( strlen($email) < 1 ){
$msg=$msg."Por favor ingrese su nombre de usuario.<BR>";
$status= "NOTOK";}
			
if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)){
$msg=$msg."Su correo electronico no es valido, ingrese uno valido.<BR>";
$status= "NOTOK";}

if ( $password <> $password2 ){
$msg=$msg."Las contraseñas no coinciden.<BR>";
$status= "NOTOK";}		


if ( $country == "" ){
$msg=$msg."Por favor seleccione un pais.<BR>";
$status= "NOTOK";}	

//Test if it is a shared client
if (!empty($_SERVER['HTTP_CLIENT_IP'])){
  $ip=$_SERVER['HTTP_CLIENT_IP'];
//Is it a proxy address
}elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
  $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
}else{
  $ip=$_SERVER['REMOTE_ADDR'];
}
//The value of $ip at this point would look something like: "192.0.34.166"
$ip = ip2long($ip);
//The $ip would now look something like: 1073732954

$sqlquery="SELECT wlink FROM settings where sno=0"; //fetching website from databse
$rec2=mysqli_query($con,$sqlquery);
$row2 = mysqli_fetch_row($rec2);
$wlink=$row2[0]; //assigning website address

$sqlquery111="SELECT etext FROM emailtext where code='SIGNUP'"; //fetching website from databse
$rec2111=mysqli_query($con,$sqlquery111);
$row2111 = mysqli_fetch_row($rec2111);
$emailtext=$row2111[0]; //assigning email text for email

if(!($package==""))
{
$sqlquery11="SELECT validity FROM packages where id = $package"; //fetching no of days validity from package table from databse
$rec211=mysqli_query($con,$sqlquery11);
$row211 = mysqli_fetch_row($rec211);
$noofdays=$row211[0]; //assigning website address
$cur=date("Y-m-d");
$expiry=date('Y-m-d', strtotime($cur. '+ '.$noofdays.'days'));
$sbonus=0;
}


if ($status=="OK") 
{
$scode=rand(1111111111,9999999999); //generating random code, this will act as signup key
$query=mysqli_query($con,"insert into affiliateuser(username,password,fname,address,email,referedby,ipaddress,mobile,doj,country,signupcode,tamount,pcktaken,expiry) values('$username','$password','$name','$address','$email','$ref','$ip','$mobile','$cur','$country','$scode','$sbonus','$package','$expiry')");
$_SESSION['paypalidsession'] = $userid;
// More headers
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= 'From: <no-reply@'.$wlink.'>' . "\r\n";
$to=$email;
$subject="Order Confirmation";
$message=$emailtext;
mail($to,$subject,$message,$headers);
print "
				<script language='javascript'>
					window.location = 'thankyou.php?username=$username';
				</script>
			"; 

}



else
{ 
$errormsg= "
<div class='alert alert-danger'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    <i class='fa fa-ban-circle'></i><strong>Please Fix Below Errors : </br></strong>".$msg."</div>"; //printing error if found in validation
					
}

}
?>
<!DOCTYPE html>
<html lang="en" class="app">
<head>
<style type="text/css">html {
    overflow-y: scroll;
background: url(images/login2.jpg) no-repeat center center fixed; 
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
}

</style>
<meta charset="utf-8" />
<title>Registrarte</title>
<meta name="google-translate-customization" content="c3c91eff8b5a0ded-878e61fea3a9f875-g9379dbb792475ecb-13"></meta>
<meta name="description" content="app, web app, responsive, admin dashboard, admin, flat, flat ui, ui kit, off screen nav" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<link rel="stylesheet" href="css/app.v1.css" type="text/css" />
<!--[if lt IE 9]> <script src="js/ie/html5shiv.js"></script> <script src="js/ie/respond.min.js"></script> <script src="js/ie/excanvas.js"></script> <![endif]-->
</head>
<body style="overflow: scroll;">
<section id="content" >
  <div class="container aside-xl"> <a class="navbar-brand block" href="#"><img src="images/icon.png" width="50%" style="margin: 15px;"/></a>
  <div class="row">
                <div class="col-sm-18">
                  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, "utf-8"); ?>" method="post" data-validate="parsley">
                    <section class="panel panel-default">
                      <header class="panel-heading">
                        <span class="h4">Registro</span>
                      </header>
                      <div class="panel-body">
					  
                        <p class="text-muted">Por favor llena toda la informacion para continuar</p>
						<?php 
						if($_SERVER['REQUEST_METHOD'] == 'POST' && ($status=="NOTOK"))
						{
						print $errormsg;
						}
						?>
						<input type="hidden" name="todo" value="post">
						<div class="form-group pull-in clearfix">
                        <div class="col-sm-6">
                          <label>Nombre de usuario</label>
                          <input type="text" class="form-control" data-required="true" name="username" value="" required>                        
                        </div>
                        <div class="col-sm-6">
                          <label>Nombre completo</label>
                         <input type="text" class="form-control" data-required="true" name="fname" required>                          
                        </div>
						</div>
                        <div class="form-group pull-in clearfix">
                          <div class="col-sm-6">
                            <label>Ingresa una contraseña</label>
                            <input type="password" class="form-control" data-required="true" id="pwd" name="password" required>   
                          </div>
                          <div class="col-sm-6">
                            <label>Confirma tu contraseña</label>
                            <input type="password" class="form-control" data-equalto="#pwd" data-required="true" name="password2" required>      
                          </div>   
                        </div>
						<div class="form-group pull-in clearfix">
						<div class="col-sm-6">
                          <label>Correo electornico</label>
                          <input type="email" class="form-control" data-type="email" data-required="true" name="email" required>                        
                        </div>
						<div class="col-sm-6">
                          <label>Telefono movil</label>
                          <input type="text" class="form-control" data-type="phone" placeholder="(XXX) XXXX XXX" data-required="true" name="mobile" required>
                        </div>
						</div>
						
                        <div class="form-group">
                          <label>Direccion</label>
                          <input type="text" class="form-control" data-required="true" name="address">
                        </div>
						
						<div class="form-group">
						<label>Pais</label>
                            <select data-required="true" class="form-control m-t" name="country" required>
                                <option value="">Escoje tu pais</option>
<option value="Brazil">Brazil</option>
<option value="Chile">Chile</option>
<option value="Colombia">Colombia</option>
<option value="Venezuela">Venezuela</option>
                            </select>
                          </div>
						  <div class="form-group">
						<label>Paquetes</label>
                            <select data-required="true" class="form-control m-t" name="package" required>
                                <option value="">Escoje un paquete</option>
								<?php $query="SELECT id,name,price,currency,tax FROM  packages"; 
 
 
 $result = mysqli_query($con,$query);

while($row = mysqli_fetch_array($result))
{
	$id="$row[id]";
	$pname="$row[name]";
	$pprice="$row[price]";
	$pcur="$row[currency]";
	$ptax="$row[tax]";
$total=$pprice+$ptax;
  print "<option value='$id'>$pname | Precio - $pcur $total </option>";
  
  }
  ?>
								</select>
                          </div>


<?php 
			if(isset($_GET["aff"])){
			$aff=mysqli_real_escape_string($con,$_GET["aff"]);
			$_SESSION['aff'] = $aff;
			
			

	}		
			
			?>
			<div class="form-group">
                          <label>Nombre de usuario de tu referido</label>
                          <input type="text" class="form-control" data-required="true" name="referral" value="<?php if (isset($_SESSION['aff'])){
			echo $_SESSION['aff']; } ?>" required>                        
                        </div>


								
                        <div class="checkbox i-checks">
                          <label>
                            <input type="checkbox" name="check" data-required="true" required><i></i> Acepto los <a href="#" class="text-info">Terminos y condiciones.</a>
                          </label>
                        </div>
                      </div>
                      <footer class="panel-footer text-right bg-light lter">
                        <button type="submit" class="btn btn-success btn-s-xs">Registrarme</button>
                      </footer>
                    </section>
					<div class="line line-dashed"></div>
          <p class="text-muted text-center"><small style="color:#ffffff;">Ya tienes una cuenta?</small></p>
          <a href="index.php" class="btn btn-lg btn-default btn-block">Ingresar</a>
                  </form>
                </div>
                
              </div>
     </div>
</section>
<!-- footer -->
<footer id="footer">
  <div class="text-center padder clearfix">
    <p> <small style="color:#ffffff;"><?php $query="SELECT footer from settings where sno=0"; 
 
 
 $result = mysqli_query($con,$query);

while($row = mysqli_fetch_array($result))
{
	$footer="$row[footer]";
	print $footer;
	}
  ?>
 </small> </p>
  </div>
</footer>
<!-- / footer -->
<!-- Bootstrap -->
<!-- App -->
<script src="js/app.v1.js"></script>
<script src="js/app.plugin.js"></script>
</body>
</html>
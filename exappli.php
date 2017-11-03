<?php // Voir https://www.w3schools.com/php/php_form_complete.asp et http://ccphp.si.fr.intraorange/precos/security/#r00 ?>
<?php

session_start();
//var_dump($_SESSION);

// Connexion a l application
require_once dirname(__FILE__).'/connecAppli.php';
require_once dirname(__FILE__).'/fonctions.php';

// Vérification de la durée de vie de la session // R01
define( 'SESSION_EXPIRE', 60 * 60 ); // 60s * 60min = 1 heure
if (isset($_SESSION['lastAccess'])) {
    // Comparaison du timestamp du dernier accès au délai d'expiration
    if ($_SESSION['lastAccess'] + SESSION_EXPIRE < time() ) {
        // RAZ de la session
        $_SESSION = array(); 
    }
}
// Renouvellement du délai d'expiration
$_SESSION['lastAccess'] = time();

// connexion et regeneration de l'id de session apres une authentification // R02
if(!isset($_SESSION['login']))  { 
    $pdo = connecDb();
    connecAppli($pdo); // connecAppli utilise les champ login et password de la table users de la BDD, $pdo pointe sur cette base
    $pdo = null;
    session_regenerate_id();
    exit;
}

//
// * * * fonctions securite



// * * * 
//

$nomErr = $paysErr = "";
$nom = $pays =  "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    //var_dump($_SESSION,$_POST);
    // jeton contre csrf verification // R20
    if (isset($_SESSION['token']) AND isset($_POST['token']) AND !empty($_SESSION['token']) AND !empty($_POST['token']) AND ($_SESSION['token'] == $_POST['token'])) {
         $token = $_SESSION['token']; // pour reaffichage caché dans le formulaire
    }
    else {
        die ("Erreur de vérification");
    }
    
    if (empty($_POST["nom"])) {
        $nameErr = "Name is required";
    } else {
        $nom = clean_input($_POST["nom"]);
        //$nom = $_POST["nom"];
        // check if name only contains letters and whitespace
        if (!preg_match("/^[a-zA-Z ]*$/",$nom)) {
            $nameErr = "Only letters and white space allowed";
        }
    }
  
    if (empty($_POST["pays"])) {
        $emailErr = "pays is required";
    } else {
        $pays = clean_input($_POST["pays"]);
    }
}else{
    // jeton contre csrf initialisation // R20
    $token = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM)); 
    $_SESSION['token'] = $token;
}

function connecDb(){
    $host   = "localhost";
    $base   = "exbase";
    $login  = "root";
    $psw    = "";
    
    try
    {
        //var_dump($login,$psw);die();
        $pdo = new PDO("mysql:host=$host;dbname=$base", "$login", $psw);
        return $pdo;
        //echo('dbo connec');
    }
    catch (Exception $e)
    {  
        die('Erreur : ' . $e->getMessage());
    }
}

// Validation des donnees utilisateur R08
function clean_input($data) {
    $data = trim($data);        // virer les blancs
    $data = stripslashes($data); // virer les anti-slash
    $data = addslashes($data); // rajouter un anti-slash devant une apostrophe pour ecrire les apostrophes dans la base
    //$data = htmlspecialchars($data);
    $data = htmlentities($data);

    return $data;
}
?>

<!DOCTYPE HTML>  
<html>
<head>
<style>
.error {color: #FF0000;}
</style>
</head>
<body>  
<h2>Securité Appli Web, le Formulaire :</h2>
<p><span class="error">* required field.</span></p>
<form method="post" action="<?php echo htmlentities($_SERVER["PHP_SELF"]);?>">  
    Nom: <input type="text" name="nom" value="<?php echo $nom;?>">
    <span class="error">* <?php echo $nomErr;?></span>
    <br><br>
    Pays: <input type="text" name="pays" value="<?php echo $pays;?>">
    <span class="error">* <?php echo $paysErr;?></span>
    <br><br>
    <input type="submit" name="submit" value="Submit">  
        <!-- jeton contre csrf , token de vérification, bien caché -->
        <input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
</form>

<?php
echo "<h2>Les Prints :</h2>";
//$mot = "baba<";
//var_dump("var_dump de mot ( variable interne ) : ",$mot);
//var_dump("var_dump de htmlentities (mot) : ",htmlentities($mot));
//echo "echo de  mot : ";
//echo ($mot);
//echo "<br>";
//echo "echo de htmlentities de mot : ";
//echo htmlentities($mot);
//echo "<br>";
//echo "<br>";

var_dump("var_dump de nom ( champs de saisie dejà nettoye avec htmlentities ) : ".$nom); //R10
var_dump("var_dump de htmlentities (nom) : ".htmlentities($nom));
echo "echo de nom : ";
echo($nom); //R10
echo "<br>";
echo "echo htmlentities de nom : ";
echo htmlentities ($nom); //R10
echo "<br>";
echo "<br>";

echo "<br>";
echo " session_id : "; echo(session_id());
echo "<br>";
echo " SESSION  : "; print_r($_SESSION);
echo "<br>";
echo " PHP_SELF  : "; print_r($_SERVER["PHP_SELF"]);
?>

</body>
</html>
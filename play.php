<?php
  if(isset($_GET['play']))
    $fileName = $_GET['play'] ;
  else
    $fileName = "" ;
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>

<?php
  echo "<audio src='https://yona-misterx22.c9users.io/translated/" . $fileName ."' autoplay>" ;
  echo "Votre navigateur ne supporte pas <code>audio</code>";
  echo "</audio>" ;
?>

</body>
</html>

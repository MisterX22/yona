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
  echo "<audio src='translated/" . $fileName ."' autoplay>" ;
  echo "Votre navigateur ne supporte pas <code>audio</code>";
  echo "</audio>" ;

  $translationtxt="translated/".$fileName.".txt";
  $recognitiontxt="translated/".$fileName.".recog";
  $contenurecog=file_get_contents($recognitiontxt); 
  $contenutrans=file_get_contents($translationtxt); 

  echo "<table border='1px' width='800px'>";
  echo "<tr>";
  echo "<th>Original Stream</th><th>Traduction Stream</th><th>Recognition text</th><th>Traduction Text</th>";
  echo "</tr>";
  echo "<tr>";
  echo "<td style='text-align: center;'><a href='uploads/" . $fileName  ."' target='_blank'>" . "&#9658;</a></td>";
  echo "<td style='text-align: center;'><a href='translated/" . $fileName  ."' target='_blank'>" . "&#9658;</a></td>";
  echo "<td>" . $contenurecog . "</td>" ;
  echo "<td>" . $contenutrans . "</td>" ;
  echo "</tr>";
  echo "</table>" ;

?>

</body>
</html>

<?php
require('fpdf/fpdf.php');
// Todo URI Server
$SERVER_API = "";
if (isset($_SERVER['HTTP_HOST'])) {
   if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $SERVER_API = "http://localhost:3001";
   } else {
    $SERVER_API = "https://panal.up.railway.app";
   }
}
$SERVER_API= $SERVER_API . "/archivos/";

// Todo */END

class PDF extends FPDF
{
    protected $col = 0; // Columna actual
    protected $y0;      // Ordenada de comienzo de la columna

// Cabecera de página
function Header()
{
    // Logo
    $title = 'Autor: Panal del trabajo';
    $this->Image('PanalCUVT.png',20,8,20);
    $title = 'Autor: Panal del trabajo';
    // Arial bold 15
    $this->SetFont('Arial','B',12);
    // Movernos a la derecha
    $this->Cell(80);
    // Título
    $this->Ln(5);
    $w = $this->GetStringWidth($title)+6;
    $this->SetX((210-$w)/2);
    $this->SetDrawColor(0,80,180);
    //$this->SetFillColor(230,230,0);
    //$this->SetTextColor(220,50,50);
    $this->SetLineWidth(1);
    $this->Cell($w,10,$title);
    $this->Ln(18);
    // Guardar ordenada
    $this->y0 = $this->GetY();
}

// Pie de página
function Footer()
{
    // Posición: a 1,5 cm del final
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Número de página
    $this->Cell(0,10,'Página '.$this->PageNo().'/{nb}',0,0,'C');
}
}

// Creación del objeto de la clase heredada
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','',14);

if ( isset( $_GET['nombre'] ) && !empty( $_GET['nombre'] ) ) {
$pdf->SetFont('Arial','B',16);
$nombre = $_GET['nombre'];
if ( isset( $_GET['apellidos'] ) && !empty( $_GET['apellidos'] ) ) {
    $nombre = $nombre." " . $_GET['apellidos'];
}
$w = $pdf->GetStringWidth($nombre);
$pdf->SetX((210-$w)/2);
$pdf->Cell(50,10,utf8_decode($nombre));
}

// Todo Foto usuario
// Logo
header("Access-Control-Allow-Origin: *");
$url=$SERVER_API.$_GET['id'];

//Iniciamos un recurso CURL en $c
$c = curl_init($url);

//Indicamos que nos devuelva la información capturada como la información de retorno
curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

//Realizamos la llamada
$img64 = curl_exec($c);
if (!empty( $img64 )) {
    // Logo
    
header("Access-Control-Allow-Origin: *");
$url=$SERVER_API.'/info/metadata/fotografia/'.$_GET['id'];

//Iniciamos un recurso CURL en $c
$cM = curl_init($url);

//Indicamos que nos devuelva la información capturada como la información de retorno
curl_setopt($cM, CURLOPT_RETURNTRANSFER, true);
//Realizamos la llamada
$metadata = curl_exec($cM);
curl_close($cM);
$rutaImagenSalida = '';
if (!empty( $metadata )) {
    $rutaImagenSalida = __DIR__ . "/".$_GET['id'].'.'.$metadata;
    if(file_exists($rutaImagenSalida)){
        unlink($rutaImagenSalida);
    }
    $imagenBinaria = base64_decode($img64);
    $bytes = file_put_contents($rutaImagenSalida, $imagenBinaria);
    $pdf->Ln(10);
    $pdf->SetXY($pdf->GetPageWidth() - (50 * 2.6),$pdf->GetY());
    $pdf->Image($_GET['id'].'.'.$metadata,$pdf->GetX(),$pdf->GetY(),50, 50);
    }
}

//Cerramos el recurso (Liberamos memoria)
curl_close($c);

// Todo end foto
$pdf -> Ln(54);
$pdf->SetFont('Arial','',14);
if ( isset( $_GET['licenciatura'] ) && !empty( $_GET['licenciatura'] ) ) {
    $w = $pdf->GetStringWidth('Carrera: '.$_GET['licenciatura']);
    $pdf->SetX((210-$w)/2);
    $pdf->Cell(50,10,'Carrera: '.utf8_decode($_GET['licenciatura']));
    $pdf-> Ln();
    }

    if ( isset( $_GET['descripcion'] ) && !empty( $_GET['descripcion'] ) ) {
        $pdf->Cell(50,10,utf8_decode('Descripción: '.$_GET['descripcion']));
        }


$pdf->Output();
if(file_exists($rutaImagenSalida)){
    unlink($rutaImagenSalida);
}

?>
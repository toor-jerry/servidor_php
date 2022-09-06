<?php
require('fpdf/fpdf.php');
// Todo URI Server
$SERVER_API = "";
if (isset($_SERVER['HTTP_HOST'])) {
    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        $SERVER_API = "http://localhost:3001";
    } else {
        $SERVER_API = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
    }
}
$SERVER_API = $SERVER_API . "/archivos/";

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
        $this->Image('PanalCUVT.png', 20, 8, 20);
        $title = 'Autor: Panal del trabajo';
        // Arial bold 15
        $this->SetFont('Arial', 'B', 12);
        // Movernos a la derecha
        $this->Cell(80);
        // Título
        $this->Ln(5);
        $w = $this->GetStringWidth($title) + 6;
        $this->SetX((210 - $w) / 2);
        $this->SetDrawColor(0, 80, 180);
        //$this->SetFillColor(230,230,0);
        //$this->SetTextColor(220,50,50);
        $this->SetLineWidth(1);
        $this->Cell($w, 10, $title);
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
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, utf8_decode('Página ' . $this->PageNo() . '/{nb}'), 0, 0, 'C');
    }
}

// Creación del objeto de la clase heredada
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times', '', 14);

if (isset($_GET['nombre']) && !empty($_GET['nombre'])) {
    $pdf->SetFont('Arial', 'B', 16);
    $nombre = $_GET['nombre'];
    if (isset($_GET['apellidos']) && !empty($_GET['apellidos'])) {
        $nombre = $nombre . " " . $_GET['apellidos'];
    }
    $w = $pdf->GetStringWidth($nombre);
    $pdf->SetX((210 - $w) / 2);
    $pdf->Cell(50, 10, utf8_decode($nombre));
}

// Todo Foto usuario
// Logo
header("Access-Control-Allow-Origin: *");
$url = $SERVER_API . $_GET['id'];

//Iniciamos un recurso CURL en $c
$c = curl_init($url);

//Indicamos que nos devuelva la información capturada como la información de retorno
curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

//Realizamos la llamada
$img64 = curl_exec($c);
if (!empty($img64)) {
    // Logo

    header("Access-Control-Allow-Origin: *");
    $url = $SERVER_API . '/info/metadata/fotografia/' . $_GET['id'];

    //Iniciamos un recurso CURL en $c
    $cM = curl_init($url);

    //Indicamos que nos devuelva la información capturada como la información de retorno
    curl_setopt($cM, CURLOPT_RETURNTRANSFER, true);
    //Realizamos la llamada
    $metadata = curl_exec($cM);
    curl_close($cM);
    $rutaImagenSalida = '';
    if (!empty($metadata)) {
        $rutaImagenSalida = __DIR__ . "/" . $_GET['id'] . '.' . $metadata;
        if (file_exists($rutaImagenSalida)) {
            unlink($rutaImagenSalida);
        }
        $imagenBinaria = base64_decode($img64);
        $bytes = file_put_contents($rutaImagenSalida, $imagenBinaria);
        $pdf->Ln(10);
        $pdf->SetXY($pdf->GetPageWidth() - (50 * 2.6), $pdf->GetY());
        $pdf->Image($_GET['id'] . '.' . $metadata, $pdf->GetX(), $pdf->GetY(), 50, 50);
    }
    $pdf->Ln(54);
} else {
    $pdf->Ln(10);
}

//Cerramos el recurso (Liberamos memoria)
curl_close($c);

// Todo end foto

$pdf->SetFont('Arial', '', 14);
if (isset($_GET['licenciatura']) && !empty($_GET['licenciatura'])) {
    $w = $pdf->GetStringWidth('Carrera: ' . $_GET['licenciatura']);
    $pdf->SetX((210 - $w) / 2);
    $pdf->Cell(50, 10, 'Carrera: ' . utf8_decode($_GET['licenciatura']));
    $pdf->Ln();
}
// Todo: Género
$genero = '';
$numeroColumna = 0;
if (isset($_GET['genero']) && !empty($_GET['genero'])) {
    $numeroColumna += 1;
    $genero = $_GET['genero'];
}

// Todo: Progreso
$pdf->SetFont('Arial', '', 14);
if (isset($_GET['progreso']) && !empty($_GET['progreso'])) {
    $tamanioLogo = 13;
    $progreso = $_GET['progreso'];
    $w = $pdf->GetStringWidth($progreso);
    $pdf->SetX((210 - $w) / 2);
    $pdf->Cell(50, 10, 'Progreso: ');
    $pdf->Ln();
    $pdf->SetX((210 - $w) / 2);
    $imagen = "assets/libros.jpg";
    if ($progreso == "Egresado") {
        $imagen = "assets/Egresado.jpg";
    } else if($genero == "Hombre") {
        $imagen = "assets/estudiante-H.jpg";
    } else if($genero == "Mujer") {
        $imagen = "assets/estudiante-M.jpg";
    }
    $pdf->Image($imagen, $pdf->GetX() - $tamanioLogo, $pdf->GetY() - 3, $tamanioLogo);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(50, 10, '  '.utf8_decode($progreso));
    $pdf->Ln();
}

$pdf->SetFont('Arial', '', 14);
if (isset($_GET['descripcion']) && !empty($_GET['descripcion'])) {
    $pdf->MultiCell(190, 10, utf8_decode('Descripción: ' . $_GET['descripcion']), 0, 'J');
}



// Todo: Experiencia laboral
$pdf->SetFont('Arial', '', 14);
if (isset($_GET['experienciaLaboral']) && !empty($_GET['experienciaLaboral'])) {
    $w = $pdf->GetStringWidth("Experiencia laboral:");
    $pdf->SetX((210 - $w) / 2);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(50, 10, 'Experiencia laboral:');
    $pdf->Ln();
    $pdf->SetFont('Arial', '', 14);
    $pdf->MultiCell(190, 10, utf8_decode($_GET['experienciaLaboral']), 0, 'J');
    $pdf->Ln();
}

// Todo: Información de contacto
$informacion = '';
if (isset($_GET['numeroContacto']) && !empty($_GET['numeroContacto'])) {
    $tamanioLogo = 15;
    $texto = 'Número de contacto: ' . $_GET['numeroContacto'];
    if (isset($_GET['email']) && !empty($_GET['email'])) {
        $texto = $texto . '  ' .'Email: ' . $_GET['email'];
    }
    $pdf->SetX($tamanioLogo + 10);
    $pdf->Image('assets/directorio_telefonico.jpg', $pdf->GetX() - $tamanioLogo, $pdf->GetY() - 3, $tamanioLogo);
    $pdf->MultiCell(180, 10, utf8_decode($texto), 0 , 'J');
   
    $pdf->Ln();
}


// Todo: Localización
if (isset($_GET['direccion']) && !empty($_GET['direccion'])) {
    $tamanioLogo = 15;
    $texto = 'Dirección: ' . $_GET['direccion'];
    $pdf->SetX($tamanioLogo + 10);

    $pdf->Image('assets/localizacion.jpg', $pdf->GetX() - $tamanioLogo, $pdf->GetY() - 3, $tamanioLogo);
    $pdf->MultiCell(180, 10, utf8_decode($texto), 0 , 'J');
   
    $pdf->Ln();
}

// Todo: ->  | Genero | Edad  | Fecha de nacimiento

$fechaNacimiento = '';
$edad = '';

if (isset($_GET['fechaNacimiento']) && !empty($_GET['fechaNacimiento'])) {
    $fechaNacimiento = $_GET['fechaNacimiento'];
    $numeroColumna += 1;
}
if (isset($_GET['edad']) && !empty($_GET['edad'])) {
    $edad = $_GET['edad'];
    $numeroColumna += 1;
}
if ($numeroColumna != 0) {
    $anchoCol = 180 / $numeroColumna;

    $pdf->SetFont('Arial', '', 14);
    if ($edad !== '') {
        $anchoColTemp = $anchoCol;
        $texto = "Edad: ".utf8_decode($edad.' Años');
        if ($numeroColumna == 3) {
            $anchoColTemp = $pdf->GetStringWidth($texto);
        }
        $pdf->Cell($anchoColTemp,6,$texto,0,0,'C',false);
    }

    if ($genero !== '') {
        $anchoColTemp = $anchoCol;
        $texto = utf8_decode("Género: ".$genero);
        if ($numeroColumna == 3) {
            $anchoColTemp = $pdf->GetStringWidth($texto);
        }
        $pdf->Cell($anchoColTemp,6,$texto,0,0,'C',false);
    }

    if ($fechaNacimiento !== '') {
        $anchoColTemp = $anchoCol;
        $texto = utf8_decode("Fecha de nacimiento: ".$fechaNacimiento);
        if ($numeroColumna == 3) {
            $anchoColTemp = $pdf->GetStringWidth($texto);
        }
        $pdf->Cell($anchoColTemp,6,$texto,0,0,'C',false);
    }
    
}

    //$pdf->Cell($anchoCelda,6,"col2",0,0,'C',false);
    //$pdf->Cell($anchoCelda,6,"col3",0,0,'C',false);
$pdf->Output();
if (file_exists($rutaImagenSalida)) {
    unlink($rutaImagenSalida);
}
?>
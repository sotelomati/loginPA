<?php

class gestorUsuarios{
    private $propietario;
    private $usuarios;
    private $cantidadUsuarios;
    

    public function __construct($idUser){
        $this->propietario = new usuario($idUser);
        $this->propietario->traerAmigos();
        $con = new mysqli('127.0.0.1','lectura','leyendoPA', 'trabajopractico1');
        $this->cantidadUsuarios = 0;
        if($con ->connect_errno){
            echo "<p class='error'>* falla coneccion base de datos</p>";    
        }else{
            $nombreUser= "SELECT nickname FROM usuarios";
            $resultado = mysqli_query($con, $nombreUser);
            
            $usuarioNuevo;
            while($row = $resultado->fetch_array()){
                if($row['nickname'] != $idUser){
                    if(!$this->propietario->isFriend($row['nickname'])){
                        $usuarioNuevo = new Usuario($row['nickname']);
                        $this->usuarios[$this->cantidadUsuarios] = $usuarioNuevo;
                        $this->cantidadUsuarios++;
                    }
                }
            }
            $con->close();
        }
    }

    public function mostrarUsuarios(){
        for($i = 0; $i < $this->cantidadUsuarios; $i++){
            $this->usuarios[$i]->mostrarAsFriend();
        }
    }
}

class Usuario{
    private $nombre;
    private $correo;
    private $sexo;
    private $ubicacion;
    private $misJuegos;
    private $amigos; 
    private $cantidadAmigos;
    private $imagen;


public function __construct($nombreUser, $principal=0){
    $con = new mysqli('127.0.0.1','lectura','leyendoPA', 'trabajopractico1');

    if($con ->connect_errno){
        echo "<p class='error'>* falla coneccion base de datos</p>";    
    }else{
        $infoPersonal = "SELECT correo, ubicaciones.nombre, sexo, imagen FROM usuarios
        INNER JOIN ubicaciones
        on usuarios.id_ubicacion = ubicaciones.id_ubicacion
        WHERE usuarios.nickname LIKE '$nombreUser'";

        $resultado = mysqli_query($con, $infoPersonal);
        $row = $resultado->fetch_array();
        
        $this->imagen = $row['imagen'];
        $this->nombre = $nombreUser;
        $this->correo = $row[0];
        $this->sexo = $row['sexo'];
        $this->ubicacion = $row['nombre'];
        if($principal){
            $_SESSION['correoLogin'] = $row[0];
        }
        
        $con->close();
}
}

public function setJuegos(){
    include 'juegos.php';
    $this->misJuegos = new gestorJuegos($this->correo);
}

public function getJuegos(){
    $this->misJuegos->mostrarGaleriaPersonal();
}

public function isFriend($supuesto){
    for($j = 0; $j<$this->cantidadAmigos; $j++){
        if($this->amigos[$j]->getNombre() == $supuesto){
            return true;
        }
    }
}

public function getNombre(){
    return $this->nombre;
}

public function traerAmigos(){
    $this->cantidadAmigos = 0;
    $con = new mysqli('127.0.0.1','lectura','leyendoPA', 'trabajopractico1');

    if($con ->connect_errno){
        echo "<p class='error'>* falla coneccion base de datos</p>";    
    }else{
        $leeAmigos = "SELECT id_usuario1, id_usuario2 FROM aliados";
        $resultado = mysqli_query($con, $leeAmigos);
        $i=0;
        $amigosCorreo;
        if($resultado){
            while($row = $resultado->fetch_array()){

                if($this->correo == $row['id_usuario1']){
                    $amigosCorreo[$this->cantidadAmigos] = $row['id_usuario2']; 
                    $this->cantidadAmigos++;
                    
                }elseif($this->correo == $row['id_usuario2']){
                    $amigosCorreo[$this->cantidadAmigos] = $row['id_usuario1']; 
                    $this->cantidadAmigos++;
                    
                }
                $i++;
                }
        }

        for($j = 0; $j<$this->cantidadAmigos; $j++){
            $resultado = mysqli_query($con, "SELECT nickname FROM usuarios WHERE correo like '$amigosCorreo[$j]'");
            $row = $resultado->fetch_array();

            $oAmigo = new usuario($row['nickname']);
            $this->amigos[$j]=$oAmigo;
        }
        $con->close();
    }
}

public function mostrarAmigos(){
    for($i = 0; $i<$this->cantidadAmigos; $i++){
        $this->amigos[$i]->mostrarInforFriend();
    }
}

public function mostrarInfor(){
    echo'
    <div class="user">
        <div style="text-align:center;"><img src="estilos/images/avatar/'.$this->imagen.'">
        <br>
        <a href="#modal" style="text-decoration:none;" >Modificar Imagen</a></div>
        <p style="color: white;"> Nombre:'.$this->nombre.'</p>
        <p style="color: white;"> Correo:'.$this->correo.'</p>
        <p style="color: white;"> Ubicacion:'.$this->ubicacion.'</p>
    </div>
    ';
}

public function mostrarInforFriend(){
    echo'
    <form class="framePersonal" action="objetos/procesos/deleteFriend.php" method="POST" >
        <div class="user">
            <div style="text-align:center;"><img src="estilos/images/avatar/'.$this->imagen.'"></div>
            <div style="align: right;">
            <input name="friendToDelete" type="hidden" value="'.$this->correo.'">
            <input style=" right: 10px; width: 30px;"; class="butonDelete" src="estilos/images/extras/delete.png" type="image" name="deleteFriend">
            </div>
            <p style="color: white;"> Nombre:'.$this->nombre.'</p>
            <p style="color: white;"> Correo:'.$this->correo.'</p>
            <p style="color: white;"> Ubicacion:'.$this->ubicacion.'</p>
        </div>
    </form>   
    ';
}

public function cambiarImagen($img){
    //echo $img;
    $conn = new mysqli('127.0.0.1','modifica','modificandoPA', 'trabajopractico1');
    $sql = "UPDATE usuarios SET imagen='$img' WHERE correo = '$this->correo'";
    $respuesta = mysqli_query($conn, $sql);
    if($respuesta){
        header("Location: inicio.php");
    }
    else{
        echo '<script>alert("ERROR AL CARGAR LA IMAGEN")</scipt>';
    }

}

public function mostrarAsFriend(){
    echo'
    <form class="frame" action="objetos/procesos/agregarAmigo.php" method="POST" >
        <div class="amigoSocial">
            <div style="text-align:center;"><img src="estilos/images/avatar/'.$this->imagen.'"></div>
            <br>
            <p style="color: white;"> Nombre:'.$this->nombre.'</p>
            <p style="color: white;"> Correo:'.$this->correo.'</p>
            <p style="color: white;"> Ubicacion:'.$this->ubicacion.'</p>
            <input name="friendToAdd" type="hidden" value="'.$this->correo.'">
        </div>
        <div style="align: right;">
        <input action="" style=" right: 10px; width: 30px;"; class="butonDelete" method="POST" src="estilos/images/extras/add.png" type="image" name="addFriend">
        </div>
    </form>
    ';
}




}



?>

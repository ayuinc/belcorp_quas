<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class usermodel extends CI_Model {
        function __construct()
        {
            // Call the Model constructor
            parent::__construct();
        }
        
        private $llave_primaria = "UserId";
        private $tabla_usuarios = "exp_usuarios";
        private $tabla_usuarios_temporal = "usuariostemp";
        private $estado_inactivo = "inactive";
        private $columna_estado = "Estado";
        
        private $moodle_tabla_usuarios = "usuarios";
        private $moodle_prefijo_tablas = "mdl_";
        private $loop = 10;
        
        private $belcorp = "http://54.243.186.233/LMSService";
        
        // CAMPOS DE LA TABLA DE USUARIOS
        public $campos = array(
                    'Estado',
                    'UserId',
                    'DNI',
                    'Nombres',
                    'Apellidos',
                    'Sexo',
                    'CorreoBelcorp',
                    'IdJefeDirecto',
                    'JefeDirecto',
                    'Direccion',
                    'Posicion',
                    'Gerencia',
                    'Vicepresidencia',
                    'FechaIngreso',
                    'TipoContrato',
                    'CodigoTrabajador',
                    'Puesto',
                    'PaisSociedad',
                    'Campania',
                    'CentroCostos',
                    'DescripcionCentroCostos',
                    'GrupoFuncional',
                    'RegionFFVV',
                    'Zona',
                    'CuadranteMatrizTalento',
                    'NivelJerarquico',
                    'UsuarioRed',
                    'Seccion',
                    'Rol',
                    'PaisGasto',
                );
        
        public function poblarTabla() {
            $DBDefault = $this->load->database('default', TRUE);
            $lista_columnas_separa_por_comas = implode(', ', $this->campos);
            
            // QUERY QUE ACTUALIZA LOS REGISTROS QUE YA EXISTEN E INSERTA LOS NUEVOS
            $query = "INSERT INTO $this->tabla_usuarios ($lista_columnas_separa_por_comas)
                SELECT $lista_columnas_separa_por_comas
                FROM $this->tabla_usuarios_temporal
                ON DUPLICATE KEY UPDATE";
            $first_column = true;
            foreach($this->campos as $campo_llave) {
                $query .= ($first_column ? " " : ", ")."`$campo_llave` = VALUES(`$campo_llave`)";
                $first_column = false;
            }
            $query .= ";";            
            $DBDefault->query($query);

            // QUERY PARA DESACTIVAR REGISTROS NO ENCONTRADOR
            $query = "DELETE FROM $this->tabla_usuarios "
                    . "WHERE $this->llave_primaria not in ("
                    . "SELECT u.$this->llave_primaria FROM $this->tabla_usuarios_temporal u"
                    . ")";
            $DBDefault->query($query);
            
            // ELIMINANDO REGISTROS CON CORREO, USUARIO DE RED O DNI EN NO APLICA
            $query = "DELETE FROM $this->tabla_usuarios ".
                    "WHERE DNI IS NULL";
            $DBDefault->query($query);
            $query = "DELETE FROM $this->tabla_usuarios ".
                    "WHERE DNI = ''";
            $DBDefault->query($query);
            $query = "DELETE FROM $this->tabla_usuarios ".
                    "WHERE UPPER(DNI) = UPPER('No Aplica')";
            $DBDefault->query($query);
            
            $query = "DELETE FROM $this->tabla_usuarios ".
                    "WHERE CorreoBelcorp IS NULL";
            $DBDefault->query($query);
            $query = "DELETE FROM $this->tabla_usuarios ".
                    "WHERE CorreoBelcorp = ''";
            $DBDefault->query($query);
            $query = "DELETE FROM $this->tabla_usuarios ".
                    "WHERE UPPER(CorreoBelcorp) = UPPER('No Aplica')";
            $DBDefault->query($query);
            
            $query = "DELETE FROM $this->tabla_usuarios ".
                    "WHERE UsuarioRed IS NULL";
            $DBDefault->query($query);
            $query = "DELETE FROM $this->tabla_usuarios ".
                    "WHERE UsuarioRed = ''";
            $DBDefault->query($query);
            $query = "DELETE FROM $this->tabla_usuarios ".
                    "WHERE UPPER(UsuarioRed) = UPPER('No Aplica')";
            $DBDefault->query($query);
            
            // TRUNCAR TEMP
            $DBDefault->query("TRUNCATE TABLE $this->tabla_usuarios_temporal");
        }
        
        public function poblarLMS_old() {
            $ini_loop = 0;
            $tabla_moodle = $this->moodle_prefijo_tablas.$this->moodle_tabla_usuarios;
            $DBDefault = $this->load->database('default', TRUE);
            $DBMoodle = $this->load->database('moodle', TRUE);
            $query = $DBDefault->get($this->tabla_usuarios, $this->loop, $ini_loop)->result_array();
            $DBMoodle->trans_start();            
            $DBMoodle->truncate($tabla_moodle);            
            while (count($query) > 0) {
                $DBMoodle->insert_batch($tabla_moodle, $query);
                $ini_loop = $ini_loop + $this->loop;
                $query = $DBDefault->get($this->tabla_usuarios, $this->loop, $ini_loop)->result_array();
            }
            
            $DBMoodle->trans_complete();
        }
        
        public function poblarLMS() {
            $file = BASEPATH."../LOG WS1-WS2.txt";
            try {
            //file_put_contents($file, "\nINICIA EL PROCESO\n\n", FILE_APPEND);
            }
            catch (Exception $ex) {                
            }
            $url = $this->belcorp;
            $myvars = '';

            $ch = curl_init($url."/index.php/webservice/iniciar");
            curl_setopt( $ch, CURLOPT_POST, 1);
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt( $ch, CURLOPT_HEADER, 0);
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec( $ch );
            try {
                //file_put_contents($file, "URL CONSUMIDA: $url/index.php/webservice/iniciar\n\n", FILE_APPEND);
            }
            catch (Exception $ex) {                
            }
            $ini_loop = 0;
            $DBDefault = $this->load->database('default', TRUE);
            $query = $DBDefault->get($this->tabla_usuarios, $this->loop, $ini_loop)->result_array();
            while (count($query) > 0) {
                //file_put_contents($file, "\nDIRECCION:".$query[0]['Direccion'], FILE_APPEND);
                $data = urlencode(json_encode($query));
                try {
                    /*file_put_contents($file, "CONSUMIENDO: $url/index.php/webservice/usuario\n\n", FILE_APPEND);
                    file_put_contents($file, "DATA: \n", FILE_APPEND);
                    file_put_contents($file, $data, FILE_APPEND);
                    file_put_contents($file, "\n\n", FILE_APPEND);*/
                }
                catch (Exception $ex) {                
                }
                $ch = curl_init($url."/index.php/webservice/usuario");
                $myvars = 'data=' . $data;
                curl_setopt( $ch, CURLOPT_POST, 1);
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
                curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt( $ch, CURLOPT_HEADER, 0);
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

                $result = curl_exec( $ch );
                
                $ini_loop = $ini_loop + $this->loop;
                $query = $DBDefault->get($this->tabla_usuarios, $this->loop, $ini_loop)->result_array();
            }
            try {
                //file_put_contents($file, "FIN DEL PROCESo\n\n", FILE_APPEND);
            }
            catch (Exception $ex) {                
            }
        }
    }
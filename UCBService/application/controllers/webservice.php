<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH.'libraries/REST_Controller.php');

class webservice extends REST_Controller {
    
    public function test_get() {
        $this->response("SERVICIO UCB ACTIVO");
    }
    
    public function usuario_get()
    {        
        echo "EJEMPLO DE JSON:<br>";
        echo "<pre>";
        echo '[
   {
      "Estado":"inactive",
      "UserId":"SF0102407326",
      "DNI":"1232407327",
      "Nombres":"PEDRO JOSE",
      "Apellidos":"MALO ROB",
      "Sexo":"M",
      "CorreoBelcorp":"pmalo@belcorp.biz",
      "IdJefeDirecto":"  11111111",
      "JefeDirecto":"BELMONT ANDERSON EDUARDO",
      "Direccion":"No Aplica",
      "Posicion":"2",
      "Gerencia":"No Aplica",
      "Vicepresidencia":"Vicepresidente Estrategia y Finanzas",
      "FechaIngreso":"2010-02-09",
      "TipoContrato":"Extranjeros indefinido",
      "CodigoTrabajador":"  00004373",
      "Puesto":"Vicepresidente Estrategia y Finanzas",
      "PaisSociedad":"Peru",
      "Campania":"PE02 CETCO S.A.",
      "CentroCostos":"15030151",
      "DescripcionCentroCostos":"Contralor\u00eda",
      "GrupoFuncional":"Adm",
      "RegionFFVV":"No Aplica",
      "Zona":"No Aplica",
      "CuadranteMatrizTalento":"No Aplica",
      "NivelJerarquico":"Vicepresidente                ",
      "UsuarioRed":"pepmalo",
      "Seccion":"No Aplica",
      "Rol":"No Aplica",
      "PaisGasto":"CORPORACION"
   },
   {
      "Estado":"active",
      "UserId":"SF0102407327",
      "DNI":"1232407327",
      "Nombres":"PEDRO JOSE",
      "Apellidos":"MALO ROB",
      "Sexo":"M",
      "CorreoBelcorp":"pmalo@belcorp.biz",
      "IdJefeDirecto":"  11111111",
      "JefeDirecto":"BELMONT ANDERSON EDUARDO",
      "Direccion":"No Aplica",
      "Posicion":"2",
      "Gerencia":"No Aplica",
      "Vicepresidencia":"Vicepresidente Estrategia y Finanzas",
      "FechaIngreso":"2010-02-09",
      "TipoContrato":"Extranjeros indefinido",
      "CodigoTrabajador":"  00004373",
      "Puesto":"Vicepresidente Estrategia y Finanzas",
      "PaisSociedad":"Peru",
      "Campania":"PE02 CETCO S.A.",
      "CentroCostos":"15030151",
      "DescripcionCentroCostos":"Contralor\u00eda",
      "GrupoFuncional":"Adm",
      "RegionFFVV":"No Aplica",
      "Zona":"No Aplica",
      "CuadranteMatrizTalento":"No Aplica",
      "NivelJerarquico":"Vicepresidente                ",
      "UsuarioRed":"pepmalo",
      "Seccion":"No Aplica",
      "Rol":"No Aplica",
      "PaisGasto":"CORPORACION\r"
   },
   {
      "Estado":"active",
      "UserId":"SF07812722",
      "DNI":"07812722",
      "Nombres":"MARIA DEL ROSARIO",
      "Apellidos":"ARIAS FLORES",
      "Sexo":"F",
      "CorreoBelcorp":"rarias@belcorp.biz",
      "IdJefeDirecto":"  11111111",
      "JefeDirecto":"BELMONT ANDERSON EDUARDO",
      "Direccion":"No Aplica",
      "Posicion":"2",
      "Gerencia":"No Aplica",
      "Vicepresidencia":"Vicepresidente Gesti\u00f3n Humana",
      "FechaIngreso":"1994-09-01",
      "TipoContrato":"Permanente \/ Indefinido",
      "CodigoTrabajador":"  00000464",
      "Puesto":"Vicepresidente Gesti\u00f3n Humana",
      "PaisSociedad":"Peru",
      "Campania":"PE02 CETCO S.A.",
      "CentroCostos":"13020151",
      "DescripcionCentroCostos":"Gesti\u00f3n Humana",
      "GrupoFuncional":"Adm",
      "RegionFFVV":"No Aplica",
      "Zona":"No Aplica",
      "CuadranteMatrizTalento":"No Aplica",
      "NivelJerarquico":"Vicepresidente                ",
      "UsuarioRed":"perarias",
      "Seccion":"No Aplica",
      "Rol":"No Aplica",
      "PaisGasto":"CORPORACION\r"
   },
   {
      "Estado":"active",
      "UserId":"SF08746457",
      "DNI":"08746457",
      "Nombres":"MARIA LOURDES",
      "Apellidos":"MONTAGNE SUERO",
      "Sexo":"F",
      "CorreoBelcorp":"lmontagne@belcorp.biz",
      "IdJefeDirecto":"  11111111",
      "JefeDirecto":"BELMONT ANDERSON EDUARDO",
      "Direccion":"No Aplica",
      "Posicion":"2",
      "Gerencia":"No Aplica",
      "Vicepresidencia":"Vicepresidente Ventas",
      "FechaIngreso":"1991-02-06",
      "TipoContrato":"Permanente \/ Indefinido",
      "CodigoTrabajador":"  00000202",
      "Puesto":"Vicepresidente Ventas",
      "PaisSociedad":"Peru",
      "Campania":"PE02 CETCO S.A.",
      "CentroCostos":"10010154",
      "DescripcionCentroCostos":"Adm VP Ventas",
      "GrupoFuncional":"Adm",
      "RegionFFVV":"No Aplica",
      "Zona":"No Aplica",
      "CuadranteMatrizTalento":"No Aplica",
      "NivelJerarquico":"Vicepresidente                ",
      "UsuarioRed":"pelmontagne",
      "Seccion":"No Aplica",
      "Rol":"GE",
      "PaisGasto":"CORPORACION\r"
   },
   {
      "Estado":"active",
      "UserId":"SF10209903879",
      "DNI":"10209903879",
      "Nombres":"MARCOS",
      "Apellidos":"RESCA DE OLIVEIRA",
      "Sexo":"M",
      "CorreoBelcorp":"mresca@belcorp.biz",
      "IdJefeDirecto":"  11111111",
      "JefeDirecto":"BELMONT ANDERSON EDUARDO",
      "Direccion":"No Aplica",
      "Posicion":"2",
      "Gerencia":"No Aplica",
      "Vicepresidencia":"Vicepresidente Operaciones",
      "FechaIngreso":"2008-11-24",
      "TipoContrato":"Salario integral",
      "CodigoTrabajador":"    349595",
      "Puesto":"Vicepresidente Operaciones",
      "PaisSociedad":"Colombia",
      "Campania":"CO01 Bel Star S.A.",
      "CentroCostos":"16060351",
      "DescripcionCentroCostos":"Abastecimiento Cluster Norte",
      "GrupoFuncional":"Adm",
      "RegionFFVV":"No Aplica",
      "Zona":"No Aplica",
      "CuadranteMatrizTalento":"No Aplica",
      "NivelJerarquico":"Vicepresidente                ",
      "UsuarioRed":"comresca",
      "Seccion":"No Aplica",
      "Rol":"No Aplica",
      "PaisGasto":"CORPORACION\r"
   },
   {
      "Estado":"active",
      "UserId":"SF10542175",
      "DNI":"10542175",
      "Nombres":"JOSE GUSTAVO",
      "Apellidos":"ARROSPIDE DEL BUSTO",
      "Sexo":"M",
      "CorreoBelcorp":"garrospide@belcorp.biz",
      "IdJefeDirecto":"  11111111",
      "JefeDirecto":"BELMONT ANDERSON EDUARDO",
      "Direccion":"No Aplica",
      "Posicion":"2",
      "Gerencia":"No Aplica",
      "Vicepresidencia":"Asesor",
      "FechaIngreso":"2012-02-04",
      "TipoContrato":"Permanente \/ Indefinido",
      "CodigoTrabajador":"  00005607",
      "Puesto":"Asesor",
      "PaisSociedad":"Peru",
      "Campania":"PE02 CETCO S.A.",
      "CentroCostos":"13010151",
      "DescripcionCentroCostos":"Direcci\u00f3n General",
      "GrupoFuncional":"Adm",
      "RegionFFVV":"No Aplica",
      "Zona":"No Aplica",
      "CuadranteMatrizTalento":"No Aplica",
      "NivelJerarquico":"Vicepresidente                ",
      "UsuarioRed":"pegarrospide",
      "Seccion":"No Aplica",
      "Rol":"No Aplica",
      "PaisGasto":"CORPORACION\r"
   },
   {
      "Estado":"active",
      "UserId":"SF114704172",
      "DNI":"114704172",
      "Nombres":"INGRID PAMELA",
      "Apellidos":"ITURRIETA BARRAZA",
      "Sexo":"F",
      "CorreoBelcorp":"iiturrieta@belcorp.biz",
      "IdJefeDirecto":"  51287401",
      "JefeDirecto":"STOPPEL GARAU LORENA",
      "Direccion":"Director Ventas",
      "Posicion":"6",
      "Gerencia":"Gerente Regional",
      "Vicepresidencia":"Vicepresidente Pa\u00edses",
      "FechaIngreso":"2008-11-24",
      "TipoContrato":"Permanente \/ Indefinido",
      "CodigoTrabajador":" 114704172",
      "Puesto":"Gerente de Zona",
      "PaisSociedad":"Chile",
      "Campania":"CH01 Promotora de Belleza S.A.",
      "CentroCostos":"10030101",
      "DescripcionCentroCostos":"Adm GZ y GR",
      "GrupoFuncional":"FFVV",
      "RegionFFVV":"11",
      "Zona":"1112",
      "CuadranteMatrizTalento":"No Aplica",
      "NivelJerarquico":"Jefe                          ",
      "UsuarioRed":"iiturrieta",
      "Seccion":"No Aplica",
      "Rol":"GZ",
      "PaisGasto":"CHILE\r"
   },
   {
      "Estado":"active",
      "UserId":"SF1749805071001",
      "DNI":"1749805071001",
      "Nombres":"RUTH GRISELDA CONSUELO",
      "Apellidos":"MUY TELLO",
      "Sexo":"F",
      "CorreoBelcorp":"rmuy@belcorp.biz",
      "IdJefeDirecto":"VACANTE",
      "JefeDirecto":"VACANTE",
      "Direccion":"Director Ventas",
      "Posicion":"6",
      "Gerencia":"Gerente Regional",
      "Vicepresidencia":"Vicepresidente Pa\u00edses",
      "FechaIngreso":"2014-01-27",
      "TipoContrato":"Permanente \/ Indefinido",
      "CodigoTrabajador":"GT00000805",
      "Puesto":"Gerente de Zona",
      "PaisSociedad":"Guatemala",
      "Campania":"GT01 Belcorp Guatemala S.A.",
      "CentroCostos":"10030101",
      "DescripcionCentroCostos":"Adm GZ y GR LB - CY",
      "GrupoFuncional":"FFVV",
      "RegionFFVV":"11",
      "Zona":"1113",
      "CuadranteMatrizTalento":"No Aplica",
      "NivelJerarquico":"Jefe                          ",
      "UsuarioRed":"rmuy",
      "Seccion":"No Aplica",
      "Rol":"GZ",
      "PaisGasto":"GUATEMALA\r"
   },
   {
      "Estado":"active",
      "UserId":"SF40153738",
      "DNI":"40153738",
      "Nombres":"JOSE ALFREDO",
      "Apellidos":"TREGEAR SALMON",
      "Sexo":"M",
      "CorreoBelcorp":"jtregear@belcorp.biz",
      "IdJefeDirecto":"  00005861",
      "JefeDirecto":"PASSANO GILLEN MELISSA ROSA",
      "Direccion":"---",
      "Posicion":"6",
      "Gerencia":"Gerente Corp Comunicaciones",
      "Vicepresidencia":"Vicepresidente Ventas",
      "FechaIngreso":"2011-02-07",
      "TipoContrato":"Permanente \/ Indefinido",
      "CodigoTrabajador":"  00005170",
      "Puesto":"L\u00edder Comunicaciones",
      "PaisSociedad":"Peru",
      "Campania":"PE02 CETCO S.A.",
      "CentroCostos":"10030854",
      "DescripcionCentroCostos":"Redaccion Ventas",
      "GrupoFuncional":"Adm",
      "RegionFFVV":"No Aplica",
      "Zona":"No Aplica",
      "CuadranteMatrizTalento":"No Aplica",
      "NivelJerarquico":"Jefe                          ",
      "UsuarioRed":"pejtregear",
      "Seccion":"No Aplica",
      "Rol":"No Aplica",
      "PaisGasto":"CORPORACION\r"
   },
   {
      "Estado":"active",
      "UserId":"SF43568468",
      "DNI":"43568468",
      "Nombres":"MARIA ANDREA",
      "Apellidos":"BUSTAMANTE ABUID",
      "Sexo":"F",
      "CorreoBelcorp":"anbustamante@belcorp.biz",
      "IdJefeDirecto":"  00004010",
      "JefeDirecto":"FERRINI OYAGUE JENNY",
      "Direccion":"Director Asuntos Corporativos",
      "Posicion":"7",
      "Gerencia":"Gerente Corp Legal",
      "Vicepresidencia":"Error silla 2451",
      "FechaIngreso":"2011-01-01",
      "TipoContrato":"Permanente \/ Indefinido",
      "CodigoTrabajador":"  00005114",
      "Puesto":"Analista Legal",
      "PaisSociedad":"Peru",
      "Campania":"PE02 CETCO S.A.",
      "CentroCostos":"15020351",
      "DescripcionCentroCostos":"Legal",
      "GrupoFuncional":"Adm",
      "RegionFFVV":"No Aplica",
      "Zona":"No Aplica",
      "CuadranteMatrizTalento":"No Aplica",
      "NivelJerarquico":"Analista                      ",
      "UsuarioRed":"peanbustamante",
      "Seccion":"No Aplica",
      "Rol":"No Aplica",
      "PaisGasto":"CORPORACION\r"
   },
   {
      "Estado":"active",
      "UserId":"SF44195044",
      "DNI":"44195044",
      "Nombres":"JORGE ALBERTO",
      "Apellidos":"JAIME LANDABURU",
      "Sexo":"M",
      "CorreoBelcorp":"jjaime@belcorp.biz",
      "IdJefeDirecto":"  00003509",
      "JefeDirecto":"PINEDO CACERES RICARDO PAOLO",
      "Direccion":"Director Corp Tecnolog\u00eda Digital",
      "Posicion":"7",
      "Gerencia":"Gerente Corp Planeamiento Digital",
      "Vicepresidencia":"Vicepresidente Tecnolog\u00eda y Servicios",
      "FechaIngreso":"2012-03-19",
      "TipoContrato":"Permanente \/ Indefinido",
      "CodigoTrabajador":"  00005650",
      "Puesto":"Analista Estrategia Digital",
      "PaisSociedad":"Peru",
      "Campania":"PE02 CETCO S.A.",
      "CentroCostos":"10030852",
      "DescripcionCentroCostos":"Estrategia Digital FFVV",
      "GrupoFuncional":"Adm",
      "RegionFFVV":"No Aplica",
      "Zona":"No Aplica",
      "CuadranteMatrizTalento":"No Aplica",
      "NivelJerarquico":"Analista                      ",
      "UsuarioRed":"pejjaime",
      "Seccion":"No Aplica",
      "Rol":"No Aplica",
      "PaisGasto":"CORPORACION\r"
   },
   {
      "Estado":"active",
      "UserId":"SF52546956",
      "DNI":"52546956",
      "Nombres":"LUISA LILIANA",
      "Apellidos":"HERNANDEZ VELEZ",
      "Sexo":"F",
      "CorreoBelcorp":"lhernandez@belcorp.biz",
      "IdJefeDirecto":"  31535230",
      "JefeDirecto":"REYES RODRIGUEZ CAROLINA MARIA",
      "Direccion":"Director Ventas",
      "Posicion":"6",
      "Gerencia":"Gerente Regional",
      "Vicepresidencia":"Vicepresidente Pa\u00edses",
      "FechaIngreso":"2012-03-05",
      "TipoContrato":"Permanente \/ Indefinido",
      "CodigoTrabajador":"  52546956",
      "Puesto":"Gerente de Zona",
      "PaisSociedad":"Colombia",
      "Campania":"CO01 Bel Star S.A.",
      "CentroCostos":"10030101",
      "DescripcionCentroCostos":"Adm GZ y GR LB - EK - CY",
      "GrupoFuncional":"FFVV",
      "RegionFFVV":"04",
      "Zona":"0428",
      "CuadranteMatrizTalento":"No Aplica",
      "NivelJerarquico":"Jefe                          ",
      "UsuarioRed":"lhernandez",
      "Seccion":"No Aplica",
      "Rol":"GZ",
      "PaisGasto":"COLOMBIA\r"
   },
   {
      "Estado":"active",
      "UserId":"SF79789798",
      "DNI":"79789798",
      "Nombres":"JUVENAL ENRIQUE",
      "Apellidos":"VELASQUEZ CERINZA",
      "Sexo":"M",
      "CorreoBelcorp":"juvelasquez@belcorp.biz",
      "IdJefeDirecto":"    424907",
      "JefeDirecto":"DA SILVA CERQUEIRA MARGARETH",
      "Direccion":"Director Corp Cient\u00edfico I&D",
      "Posicion":"6",
      "Gerencia":"Gerente Corp I&D Qu\u00edmico",
      "Vicepresidencia":"Vicepresidente Marcas e Innovaci\u00f3n",
      "FechaIngreso":"2008-02-27",
      "TipoContrato":"Permanente \/ Indefinido",
      "CodigoTrabajador":"  79789798",
      "Puesto":"Cient\u00edfico Principal Desarrollo Proyectos",
      "PaisSociedad":"Colombia",
      "Campania":"CO01 Bel Star S.A.",
      "CentroCostos":"17060251",
      "DescripcionCentroCostos":"Desarrollo Qu\u00edmico Cuidado Personal",
      "GrupoFuncional":"Adm",
      "RegionFFVV":"No Aplica",
      "Zona":"No Aplica",
      "CuadranteMatrizTalento":"No Aplica",
      "NivelJerarquico":"Jefe                          ",
      "UsuarioRed":"cojuvelasquez",
      "Seccion":"No Aplica",
      "Rol":"No Aplica",
      "PaisGasto":"CORPORACION\r"
   }
]';
        echo "</pre>";
        return;
    }
    
    public function usuario_post() {
        try 
        {
            $this->load->model("usermodel");
            $this->usermodel->poblarTabla();          
        }
        catch (Exception $e) {
            file_put_contents(BASEPATH."../csv/ERROR.log", print_r($e));
            $this->response("ERROR AL ACTUALIZAR REGISTROS");
        }
        $this->response("REGISTROS ACTUALIZADOS");
    }
   
    public function lms_post() {
        $this->insertar_lms();
    }
    
    public function lms_get() {
        $this->insertar_lms();
    }
    
    public function insertar_lms() {
        try 
        {
            $this->load->model("usermodel");
            $this->usermodel->poblarLMS();
        }
        catch (Exception $e) {
            file_put_contents(BASEPATH."../csv/ERROR.log", print_r($e));
            $this->response("ERROR AL ACTUALIZAR REGISTROS");
        }
        $this->response("REGISTROS ACTUALIZADOS");
    }
}
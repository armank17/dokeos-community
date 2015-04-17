<?php
/**
 * 
 *
 * @author achavez
 */
class TemplateManager {
    const OPERATION_ONLY_LANGUAGES = 1;
    const OPERATION_EXCLUDE_LANGUAGES = 2;
    
    static public function addTemplatesToAccessUrlId($access_url_id, array $languages = NULL, $type_operation = self::OPERATION_ONLY_LANGUAGES){
        $tbl_node          = Database::get_main_table(TABLE_MAIN_NODE);
        $tbl_node_homepage = Database::get_main_table(TABLE_MAIN_NODE_HOMEPAGE);
        $tbl_languages 	   = Database::get_main_table(TABLE_MAIN_LANGUAGE);
        
        $sql = "SELECT MAX(id) as max_id FROM $tbl_node;";
        $rs  = Database::query($sql);
        $row = Database::fetch_array($rs, 'ASSOC');
        $node_id = $row['max_id'];
        
        $sql = "SELECT * FROM $tbl_languages";
        $rs  = Database::query($sql);
        
        $content = Database::escape_string('<style>    .textCenterindex {        border: 1px solid #CCCCCC;        margin-top: 40px;        padding: 20px;    }    .textCenterindex h1{        background-color: #FFFFFF;        margin: -30px auto 20px;        max-width: 70%;        min-width: 40%;        padding-left: 20px;        padding-right: 20px;        text-align: center;        width: 40%;    }    #page_template ul{        margin:0px;        padding: 0px;    }    #page_template li {        background:#eaeaea;        border:1px solid #cccccc;        padding:15px;        margin-top:10px;        list-style: none;        padding-left: 20px;    }</style><div id="page_template" style="margin-bottom: 30px;    width: 720px;">    <div class="textCenterindex">        <h1>Unique selling proposition</h1>        <ul>            <li>                First point of difference where your training program focusses on so and so advantages.             </li>            <li>                Second point of difference making the your logo experience a unique experience.            </li>            <li>                Third point of difference and the reason why so many people and organizations select your logo.            </li>        </ul>    </div></div>');
    
        while($language = Database::fetch_array($rs, 'ASSOC')){
            if ($language['isocode'] == 'en'){
                $content = Database::escape_string('<style>    .textCenterindex {        border: 1px solid #CCCCCC;        margin-top: 40px;        padding: 20px;    }    .textCenterindex h1{        background-color: #FFFFFF;        margin: -30px auto 20px;        max-width: 70%;        min-width: 40%;        padding-left: 20px;        padding-right: 20px;        text-align: center;        width: 40%;    }    #page_template ul{        margin:0px;        padding: 0px;    }    #page_template li {        background:#eaeaea;        border:1px solid #cccccc;        padding:15px;        margin-top:10px;        list-style: none;        padding-left: 20px;    }</style><div id="page_template" style="margin-bottom: 30px;    width: 720px;">    <div class="textCenterindex">        <h1>Unique selling proposition</h1>        <ul>            <li>                First point of difference where your training program focusses on so and so advantages.             </li>            <li>                Second point of difference making the your logo experience a unique experience.            </li>            <li>                Third point of difference and the reason why so many people and organizations select your logo.            </li>        </ul>    </div></div>');
            }
            elseif($language['isocode'] == 'es'){
                $content = Database::escape_string('<style>    .textCenterindex {        border: 1px solid #CCCCCC;        margin-top: 40px;        padding: 20px;    }    .textCenterindex h1{        background-color: #FFFFFF;        margin: -30px auto 20px;        max-width: 70%;        min-width: 40%;        padding-left: 20px;        padding-right: 20px;        text-align: center;        width: 40%;    }    #page_template ul{        margin:0px;        padding: 0px;    }    #page_template li {        background:#eaeaea;        border:1px solid #cccccc;        padding:15px;        margin-top:10px;        list-style: none;        padding-left: 20px;    }</style><div id="page_template" style="margin-bottom: 30px;    width: 720px;">    <div class="textCenterindex">        <h1>Propuesta &uacute;nica de venta</h1>        <ul>            <li>               Primer punto de diferencia, donde su programa de entrenamiento se centra en tal y tal ventaja.    </li>            <li>                El segundo punto de diferencia, hacer su experiencia de logotipo, una experiencia &uacute;nica.           </li>            <li>                Tercer punto de diferencia, y la raz&oacute;n por qu&eacute; tantas personas y organizaciones seleccionan su logotipo.            </li>        </ul>    </div></div>');
            }
            elseif($language['isocode'] == 'fr'){
                $content = Database::escape_string('<style>    .textCenterindex {        border: 1px solid #CCCCCC;        margin-top: 40px;        padding: 20px;    }    .textCenterindex h1{        background-color: #FFFFFF;        margin: -30px auto 20px;        max-width: 70%;        min-width: 40%;        padding-left: 20px;        padding-right: 20px;        text-align: center;        width: 40%;    }    #page_template ul{        margin:0px;        padding: 0px;    }    #page_template li {        background:#eaeaea;        border:1px solid #cccccc;        padding:15px;        margin-top:10px;        list-style: none;        padding-left: 20px;    }</style><div id="page_template" style="margin-bottom: 30px;    width: 720px;">    <div class="textCenterindex">        <h1>Proposition de vente unique </h1>        <ul>            <li>              Premier point de diff&eacute;rence l&agrave; o&ugrave; votre programme de formation met l\'accent sur telle ou telle avantages.          </li>            <li>               Deuxi&egrave;me point de diff&eacute;rence faire la votre exp&eacute;rience de logo une exp&eacute;rience unique.            </li>            <li>                Troisi&egrave;me point de diff&eacute;rence et la raison pour laquelle tant de gens et d\'organisations s&eacute;lectionner votre logo.            </li>        </ul>    </div></div>');
            }
            else{
                $content = Database::escape_string('<style>    .textCenterindex {        border: 1px solid #CCCCCC;        margin-top: 40px;        padding: 20px;    }    .textCenterindex h1{        background-color: #FFFFFF;        margin: -30px auto 20px;        max-width: 70%;        min-width: 40%;        padding-left: 20px;        padding-right: 20px;        text-align: center;        width: 40%;    }    #page_template ul{        margin:0px;        padding: 0px;    }    #page_template li {        background:#eaeaea;        border:1px solid #cccccc;        padding:15px;        margin-top:10px;        list-style: none;        padding-left: 20px;    }</style><div id="page_template" style="margin-bottom: 30px;    width: 720px;">    <div class="textCenterindex">        <h1>Unique selling proposition</h1>        <ul>            <li>                First point of difference where your training program focusses on so and so advantages.             </li>            <li>                Second point of difference making the your logo experience a unique experience.            </li>            <li>                Third point of difference and the reason why so many people and organizations select your logo.            </li>        </ul>    </div></div>');
            }
            if (isset($languages)){
                if ($type_operation == self::OPERATION_ONLY_LANGUAGES){
                    if (array_search($language['id'], $languages) === false){
                        continue;
                    }
                } else if ($type_operation == self::OPERATION_EXCLUDE_LANGUAGES){
                    if (array_search($language['id'], $languages) !== false){
                        continue;
                    }
                }
            }            
            
            $node_id++;
            
            $language_id = $language['id'];
            $title       = 'Home'; //get_lang('Home', 'DLTT', $language['dokeos_folder']);
            
            $sql = "INSERT INTO $tbl_node (id, title, content, access_url_id, created_by, modified_by, creation_date, modification_date, active, language_id, enabled, node_type, display_title) VALUES ($node_id, '$title', '$content', $access_url_id, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 1, $language_id, 1, ". NODE_HOMEPAGE .", 0);";
            Database::query($sql);
            $sql = "INSERT INTO $tbl_node_homepage (node_id, promoted) VALUES ($node_id,  1);";
            Database::query($sql);
        }
        
        return $node_id;
    }
}

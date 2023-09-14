<?
namespace Voyadger\Seo;

use \Bitrix\Main\Entity;
use \Bitrix\Main\Type;

class RedirectsTable extends Entity\DataManager
{
    public static function getTableName(){
        return 'voyadger_seo_redirects';
    }

    public static function getConnectionName(){
        return 'default';
    }

    public static function getMap(){

        return array(
            // ID
            new Entity\IntegerField('ID', array(
                    'primary' => true,
                    'autocomplete' => true
                )
            ),

            // Active
            new Entity\BooleanField('active', array(
                'values' => array('N', 'Y')
            )),

            // URL FROM
            new Entity\StringField('url_from', array(
                'required' => true
            )),

            // URL TO
            new Entity\StringField('url_to', array(
                'required' => true
            )),

            new Entity\EnumField('redirect_type', array(
                'values' => array('301', '302')
            ))

        );
    }

}

?>
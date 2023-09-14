<?
namespace Voyadger\Seo;

use \Bitrix\Main\Entity;
use \Bitrix\Main\Type;

class UrlsTable extends Entity\DataManager
{
    public static function getTableName(){
        return 'voyadger_seo_urls';
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

            // Title
            new Entity\StringField('title', array(
                'required' => false
            )),

            // Keywords
            new Entity\StringField('keywords', array(
                'required' => false
            )),

            // Description
            new Entity\StringField('description', array(
                'required' => false
            )),

            // H1
            new Entity\StringField('h1', array(
                'required' => false
            )),

            // Text
            new Entity\StringField('text', array(
                'required' => false
            )),

            // URL
            new Entity\StringField('url', array(
                'required' => true
            )),

        );
    }

}

?>
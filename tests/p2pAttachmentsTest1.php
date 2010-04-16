<?php

define( 'MEDIAWIKI', true );
require_once 'p2pBot.php';
require_once 'BasicBot.php';
include_once 'p2pAssert.php';
require_once '../../../includes/GlobalFunctions.php';
require_once '../patch/Patch.php';
require_once '../files/utils.php';

$wgDebugLogGroups  = array(
        'p2p'=>"/tmp/p2p.log",
);

/**
 * Description of p2pAttachmentTest1
 *
 * @author Fréderique Guillaume & Émile Morel
 */


class p2pAttachmentTest1 extends PHPUnit_Framework_TestCase {

    var $p2pBot1;
    var $p2pBot2;
    var $p2pBot3;

    var $pageName = "Ours";
    var $pushName = 'PushAnimal';
    var $pullName = 'PullAnimal';
    var $pushRequest = '[[Category:Animal]]';
    var $pushFeed = 'PushFeed:PushOurs';
    var $pullFeed = 'PullFeed:PullOurs';
    var $fileDir = 'Import/';
    var $file = 'Ours.jpg';
    var $file1 = 'Ours1.jpg';
    var $file2 = 'Ours2.jpg';
    var $file3 = 'Ours3.jpg';
    var $file_size1;
    var $file_size2;
    var $file_size3;
    var $content="Les ours (ou ursinés, du latin ŭrsus, de même sens) sont de grands
mammifères plantigrades appartenant à la famille des ursidés. Il
n'existe que huit espèces d'ours vivants, mais ils sont largement
répandus et apparaissent dans une grande variété d'habitats, aussi
bien dans l'hémisphère nord qu'une partie de l'hémisphère sud. Les
ours vivent dans les continents d'Europe, d'Amérique du Nord,
d'Amérique du Sud, et en Asie.
[[Image:ours.jpg|right|frame|Un Ours]]
[[Category:Animal]]";

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp() {
        exec('./initWikiTest.sh');
        exec('rm ./cache/*');
        $basicbot1 = new BasicBot();
        $basicbot1->wikiServer = 'http://localhost/wiki1';
        $this->p2pBot1 = new p2pBot($basicbot1);

        $basicbot2 = new BasicBot();
        $basicbot2->wikiServer = 'http://localhost/wiki2';
        $this->p2pBot2 = new p2pBot($basicbot2);

        $basicbot3 = new BasicBot();
        $basicbot3->wikiServer = 'http://localhost/wiki3';
        $this->p2pBot3 = new p2pBot($basicbot3);

        // trois fichiers images de tailles differentes pour les reconnaitres.
        $this->file_size1 = filesize($this->fileDir.$this->file1);
        $this->file_size2 = filesize($this->fileDir.$this->file2);
        $this->file_size3 = filesize($this->fileDir.$this->file3);

        // je ne sais pas pourquoi mais il faut initialiser les 3 wiki sinon
        // on a un "Unable to connect" (en tous cas chez moi)
        $this->p2pBot1->createPage("init","test");
        $this->p2pBot2->createPage("init","test");
        $this->p2pBot3->createPage("init","test");
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown() {
        $this->viderRepertoire();
        //exec('./deleteTest.sh');
    }


    /**
     * Create one page with an attachment, push it
     * ne sert pas a grand chose si ce n'est à être appelé par d'autres methodes
     */
    public function testSimple1() {
        //create page
        $this->assertTrue($this->p2pBot1->createPage($this->pageName,$this->content),
                'Failed to create page '.$this->pageName.' ('.$this->p2pBot1->bot->results.')');

        //create push on wiki1
        $this->assertTrue($this->p2pBot1->createPush($this->pushName, $this->pushRequest),
                'Failed to create push : '.$this->pushName.' ('.$this->p2pBot1->bot->results.')');

        //push 
        $this->assertTrue($this->p2pBot1->push('PushFeed:'.$this->pushName),
                'failed to push '.$this->pushName.' ('.$this->p2pBot2->bot->results.')');

        // assert page Ours exist on wiki1
        assertPageExist($this->p2pBot1->bot->wikiServer, $this->pageName);

        //upload the file on wiki1
        $this->assertTrue($this->p2pBot1->uploadFile($this->fileDir.$this->file1,$this->file,'0'));

        //test si le fichier a été uploder au bon endroit lors du push
        $path = $this->p2pBot1->getHashPathForLevel($this->file,2);
        $this->assertTrue(file_exists('../../../images/'.$path.$this->file));
        $this->assertTrue(filesize('../../../images/'.$path.$this->file) ==$this->file_size1);
    }

    /**
     * Create one page with an attachment, push it
     * pull it on wiki2
     * wiki2 must have the attachment on local
     */
    public function testSimple2() {
        $this->testSimple1();

        //create pull on wiki2
        $this->assertTrue($this->p2pBot2->createPull($this->pullName,'http://localhost/wiki1', $this->pushName),
                'failed to create pull '.$this->pullName.' ('.$this->p2pBot2->bot->results.')');

        //pull
        $this->assertTrue($this->p2pBot2->Pull('PullFeed:'.$this->pullName),
                'failed to pull '.$this->pullName.' ('.$this->p2pBot2->bot->results.')');

        // assert page Ours exist on wiki2
        assertPageExist($this->p2pBot2->bot->wikiServer, $this->pageName);

        //C'est la qu'il va faloir modifier le code du DSMW pour telecharger le fichier en locale
        //pour prouver que ca marche:
        //$this->assertTrue($this->p2pBot2->uploadFile($this->fileDir.$this->file1,$this->file,'0'));

        //test if the good file was upload on wiki2
        $path = $this->p2pBot2->getHashPathForLevel($this->file,2);
        $this->assertTrue(file_exists('../../../../wiki2/images/'.$path.$this->file));
        $this->assertTrue(filesize('../../../../wiki2/images/'.$path.$this->file) == $this->file_size1);

    }

    /**
     * Create one page with an attachment, push it
     * pull it on wiki2
     * wiki2 change the file, pull the page
     * wiki1 push the page and must have the new attachment on local
     */
    public function testSimple3() {
        $this->testSimple2();

        //change file on wiki2
        $this->assertTrue($this->p2pBot2->uploadFile($this->fileDir.$this->file2,$this->file,'1'));
                
        //test si le fichier a été uploder sur le wiki2
        $path = $this->p2pBot2->getHashPathForLevel($this->file,2);
        $this->assertTrue(file_exists('../../../../wiki2/images/'.$path.$this->file));
        $this->assertTrue(filesize('../../../../wiki2/images/'.$path.$this->file) == $this->file_size2);

        //create push on wiki2
        $this->assertTrue($this->p2pBot2->createPush($this->pushName, $this->pushRequest),
                'Failed to create push : '.$this->pushName.' ('.$this->p2pBot1->bot->results.')');

        //push 
        $this->assertTrue($this->p2pBot2->push('PushFeed:'.$this->pushName),
                'failed to push '.$this->pushName.' ('.$this->p2pBot2->bot->results.')');

        //create pull on wiki1 from wiki2
        $this->assertTrue($this->p2pBot1->createPull($this->pullName,'http://localhost/wiki2', $this->pushName),
                'failed to create pull '.$this->pullName.' ('.$this->p2pBot1->bot->results.')');

        //pull
        $this->assertTrue($this->p2pBot1->Pull('PullFeed:'.$this->pullName),
                'failed to pull '.$this->pullName.' ('.$this->p2pBot1->bot->results.')');

        //C'est la qu'il va faloir modifier le code du DSMW pour telecharger le fichier en locale
        //pour prouver que ca marche:
        //$this->assertTrue($this->p2pBot1->uploadFile($this->fileDir.$this->file2,$this->file,'1'));
        
        //test if the good file was upload on wiki1 from wiki2
        $path = $this->p2pBot1->getHashPathForLevel($this->file,2);
        $this->assertTrue(file_exists('../../../images/'.$path.$this->file));
        $this->assertTrue(filesize('../../../images/'.$path.$this->file) == $this->file_size2);
    }

    /**
     * Create one page with an attachment, push it
     * pull it on wiki2 and on wiki3
     * modify the attachment on wiki2 and wiki3
     * pull on wiki1
     * wiki1 must have the good attachment on local
     */
    public function testSimple4() {
        $this->testSimple1();

        //create pull on wiki2
        $this->assertTrue($this->p2pBot2->createPull($this->pullName,'http://localhost/wiki1', $this->pushName),
                'failed to create pull '.$this->pullName.' ('.$this->p2pBot2->bot->results.')');

        //pull
        $this->assertTrue($this->p2pBot2->Pull('PullFeed:'.$this->pullName),
                'failed to pull '.$this->pullName.' ('.$this->p2pBot2->bot->results.')');

        // assert page Ours exist on wiki2
        assertPageExist($this->p2pBot2->bot->wikiServer, $this->pageName);

        //create pull on wiki3
        $this->assertTrue($this->p2pBot3->createPull($this->pullName,'http://localhost/wiki1', $this->pushName),
                'failed to create pull '.$this->pullName.' ('.$this->p2pBot3->bot->results.')');

        //pull
        $this->assertTrue($this->p2pBot3->Pull('PullFeed:'.$this->pullName),
                'failed to pull '.$this->pullName.' ('.$this->p2pBot3->bot->results.')');

        // assert page Ours exist on wiki3
        assertPageExist($this->p2pBot3->bot->wikiServer, $this->pageName);




        //change file on wiki2
        $this->assertTrue($this->p2pBot2->uploadFile($this->fileDir.$this->file2,$this->file,'1'));

        //create push on wiki2
        $this->assertTrue($this->p2pBot2->createPush($this->pushName, $this->pushRequest,'true'),
                'Failed to create push : '.$this->pushName.' ('.$this->p2pBot2->bot->results.')');

        //push
        $this->assertTrue($this->p2pBot2->push('PushFeed:'.$this->pushName),
                'failed to push '.$this->pushName.' ('.$this->p2pBot2->bot->results.')');

        //change file on wiki3
        $this->assertTrue($this->p2pBot3->uploadFile($this->fileDir.$this->file3,$this->file,'1'));

        //create push on wiki3
        $this->assertTrue($this->p2pBot3->createPush($this->pushName, $this->pushRequest),
                'Failed to create push : '.$this->pushName.' ('.$this->p2pBot3->bot->results.')');

        //push 
        $this->assertTrue($this->p2pBot3->push('PushFeed:'.$this->pushName),
                'failed to push '.$this->pushName.' ('.$this->p2pBot3->bot->results.')');




        //create pull on wiki1 from wiki2
        $this->assertTrue($this->p2pBot1->createPull($this->pullName,'http://localhost/wiki2', $this->pushName),
                'failed to create pull '.$this->pullName.' ('.$this->p2pBot1->bot->results.')');

        //pull
        $this->assertTrue($this->p2pBot1->Pull('PullFeed:'.$this->pullName),
                'failed to pull '.$this->pullName.' ('.$this->p2pBot1->bot->results.')');

        //create pull on wiki1 from wiki3
        $this->assertTrue($this->p2pBot1->createPull($this->pullName,'http://localhost/wiki3', $this->pushName),
                'failed to create pull '.$this->pullName.' ('.$this->p2pBot1->bot->results.')');

        //pull
        $this->assertTrue($this->p2pBot1->Pull('PullFeed:'.$this->pullName),
                'failed to pull '.$this->pullName.' ('.$this->p2pBot1->bot->results.')');

        //C'est la qu'il va faloir modifier le code du DSMW pour telecharger le fichier en locale
        //choix d'un algo pour faire le choix entre plusieurs fichiers joint
        //pour prouver que ca marche:
        //$this->assertTrue($this->p2pBot1->uploadFile($this->fileDir.$this->file3,$this->file,'1'));
        
        //test if the good file was upload on wiki1 from wiki3
        $path = $this->p2pBot1->getHashPathForLevel($this->file,2);
        $this->assertTrue(file_exists('../../../images/'.$path.$this->file));
        $this->assertTrue(filesize('../../../images/'.$path.$this->file) == $this->file_size3);
    }

    function viderRepertoire(){
        //donner les droits du group www-data à l'utilisateur 
        $path = $this->p2pBot1->getHashPathForLevel($this->file,1);
        exec('rm -rf ../../../images/'.$path);
        exec('rm -rf ../../../../wiki2/images/'.$path);
        exec('rm -rf ../../../../wiki3/images/'.$path);
        exec('rm -rf ../../../images/archive/'.$path);
        exec('rm -rf ../../../../wiki2/images/archive/'.$path);
        exec('rm -rf ../../../../wiki3/images/archive/'.$path);
        exec('rm -rf ../../../images/thumb/'.$path);
        exec('rm -rf ../../../../wiki2/images/thumb/'.$path);
        exec('rm -rf ../../../../wiki3/images/thumb/'.$path);
    }
}

?>

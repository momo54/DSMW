<?php

if (!defined('MEDIAWIKI')) {
    define('MEDIAWIKI', true);
}
$wgDebugLogFile  = "debug.log";
$wgDebugLogGroups  = array(
    'p2p'     => "debug-p2p-t2.log",
    'ed'      => "debug-ed-t2.log"
);

if (!defined('LOGOOTMODE')) {
    //define('LOGOOTMODE', 'STD');
    define('LOGOOTMODE', 'PLS');
}

// <ED> =====================================================================
if (!defined('DIGIT')) {
    define('DIGIT', 2);
}
if (!defined('INT_MAX')) {
    define('INT_MAX', (integer) pow(10, DIGIT));
}
if (!defined('INT_MIN')) {
    define('INT_MIN', 0);
}
if (!defined('BASE')) {
    define('BASE', (integer) (INT_MAX - INT_MIN));
}

if (!defined('CLOCK_MAX')) {
    define('CLOCK_MAX', "100000000000000000000000");
}
if (!defined('CLOCK_MIN')) {
    define('CLOCK_MIN', "0");
}

if (!defined('SESSION_MAX')) {
    define('SESSION_MAX', "FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF");//.CLOCK_MAX);
                         //050F550EB44F6DE53333AE460EE85396
}
if (!defined('SESSION_MIN')) {
    define('SESSION_MIN', "0");
}

if (!defined('BOUNDARY')) {
    define('BOUNDARY', (integer) pow(10, DIGIT / 2));
}
// </ED> ====================================================================

require_once '../../logootComponent/Math/BigInteger.php';

require_once '../../logootComponent/LogootOperation.php';
require_once '../../logootComponent/LogootPlusOperation.php';
require_once '../../logootComponent/LogootId.php';
require_once '../../logootComponent/LogootPosition.php';
require_once '../../logootComponent/logoot.php';
require_once '../../logootComponent/logootPlus.php';
require_once '../../logootComponent/logootEngine.php';
require_once '../../logootComponent/logootPlusEngine.php';
require_once '../../logootComponent/LogootIns.php';
require_once '../../logootComponent/LogootDel.php';
require_once '../../logootComponent/LogootPlusIns.php';
require_once '../../logootComponent/LogootPlusDel.php';
require_once '../../logootComponent/LogootPatch.php';

require_once '../../logootComponent/DiffEngine.php';
//require_once '../../logootComponent/BigInteger.php';

require_once '../../logootModel/boModel.php';
require_once '../../logootModel/dao.php';
require_once '../../logootModel/manager.php';
require_once '../../logootModel/boModelPlus.php';

require_once '../../../../includes/GlobalFunctions.php';
require_once 'utils.php';

class logootTest2 extends PHPUnit_Framework_TestCase {

protected $patch = array();
protected $texte1, $texte2, $texte3;

protected function setUp() {
$this->texte1 = <<<txt
txt;

$this->texte2 = <<<txt
  ligne1
  ligne2
  ligne3
  ligne4
  ligne5
  ligne6
txt;

$this->texte3 = <<<txt
  ligne1
  ligne2
  ligne7
txt;

$this->texte4 = <<<txt
  ligne1
  ligne3
txt;


$this->texte5 = <<<txt
  ligne1
  ligne3
  ligneFin 1
  ligneFin 2
  ligneFin 3
txt;

$this->texte6 = <<<txt
  ligneDebut 1
  ligneDebut 2
  ligne1
  ligne3
  ligneFin 1
  ligneFin 2
  ligneFin 3
txt;

$this->texte7 = <<<txt
  ligneAutre 1
  ligneDebut 1
  ligneDebut 2
  ligne1
  ligne3
  ligneFin 1
  ligneFin 2
  ligneFin 3
  ligneAutre 2
txt;

$this->texte8 = <<<txt
  Leslie Lamport est un chercheur en informatique américain, spécialiste de l'algorithmique répartie. Il est né en 1941 à New York et a fait des études en mathématiques au Massachusetts Institute of Technology (MIT) puis à l'université de Brandeis.
  Il a notamment formulé en 1979 la relation « arrivé-avant » (en anglais « happened before »), qui permet d'obtenir un ordre partiel sur les actions dans des systèmes répartis. Lamport a également inventé le système d'horloges qui porte son nom. Elles sont utilisées dans le cadre de la synchronisation des systèmes distribués. Il a également travaillé sur certains aspects cryptographiques comme les mots de passe à usage unique et d'autres problèmes liés au consensus et à la concurrence dans les réseaux.
  Cependant, Leslie Lamport est principalement connu hors de la communauté scientifique de l'informatique comme le créateur de LaTeX (basé lui-même sur TeX, de Donald Knuth), un système de mise en page de documents notamment utilisé par les scientifiques de nombreuses disciplines (informatique, mathématiques, physique, bio-informatique...) pour des documents comportant de nombreuses formules mathématiques. Depuis, le développement de LaTeX est assuré par d'autres.

  Il travaille depuis 2001 pour Microsoft Research.

  En 2008, il reçoit la médaille John von Neumann1.
txt;

$this->texte9 = <<<txt
  Algorithme de la boulangerie

  Aller à : Navigation, rechercher

  L'algorithme de la boulangerie ((en)Lamport's bakery algorithm) est un algorithme d'exclusion mutuelle découvert par Leslie Lamport1, dans le cadre général de machines multi-processeurs à mémoire partagée ne fournissant aucune opération atomique.
  Dans sa forme originelle, il utilise de l'attente active avant l'entrée en section critique.

  Utilité[modifier]

  L'algorithme de la boulangerie peut être utilisé afin de réaliser une exclusion mutuelle sur toute machine multi-processeur, y compris celles qui ne fournissent pas d'opérations atomiques, ou qui en fournissent de simples ne permettant de réaliser qu'une seule opération mémoire (lecture ou écriture) à la fois.
  Cependant, toutes les architectures modernes proposent des opérations atomiques combinant à la fois la lecture et l'écriture en une seule opération (comme Test And Set, Fetch And Add ou Compare And Swap). Ces dernières autorisent des implémentations de l'exclusion mutuelle plus efficaces.
  L'algorithme de la boulangerie présente donc aujourd'hui principalement un intérêt théorique et pédagogique (lire la section Historique et Propriétés pour plus de détails).

  Intuition[modifier]

  L'algorithme reprend l'intuition de la gestion d'une file d'attente dans un petit commerce (boulangerie) ou une administration (préfecture). Des numéros d'ordre croissants sont attribués (implicitement ou par des tickets) aux clients / usagers au fur et à mesure qu'ils se présentent, et ces derniers sont servis dans l'ordre des numéros.
  Les différents fils d'exécution (ou processus) souhaitant entrer en section critique sont donc les analogues des clients / usagers. L'algorithme comporte schématiquement 3 phases:
  Attribution d'un numéro d'ordre (ticket).

  Attente de son tour avant l'entrée en section critique.

  Sortie de la section critique.

  L'analogie ne peut cependant être poursuivie car, contrairement à ce qui se passe dans la vie courante, plusieurs fils d'exécution peuvent occasionnellement obtenir le même numéro d'ordre lors de la première phase, ce qui nécessite un arbitrage ultérieur.

  L'algorithme[modifier]

  L'algorithme garantit l'exclusion mutuelle de N fils d'exécution, N étant fixé à l'avance.

  Les fils doivent posséder des identifiants (habituellement, un entier) comparables (c'est-à-dire éléments d'un ensemble munis d'une relation d'ordre, supposée totale).
  L'algorithme requiert également une zone de mémoire partagée servant à stocker deux tableaux comportant N éléments:
  Le premier est un tableau de booléens, appelé CHOIX.
  Le second est un tableau d'entiers naturels, appelé COMPTEUR.

  La valeur particulière 0 indique, dans ce dernier tableau, qu'un fil ne souhaite pas entrer en section critique. Les autres valeurs représentent des numéros de ticket potentiels.
  Il n'est pas nécessaire que les opérations de lecture et d'écriture sur les tableaux partagés soient réalisées de manière atomique. Pour faciliter la compréhension, le lecteur peut dans un premier temps supposer que ces opérations sont atomiques, puis se référer à la section Historique et Propriétés pour obtenir des précisions sur la validité de ce point.
  L'algorithme ne présuppose rien sur la vitesse d'exécution relative des différents fils. Il garantit qu'un seul fil exécute la section critique à la fois. Il n'introduit pas d'interblocage ou de famine.

  Initialisation[modifier]

  Avant toute utilisation de l'algorithme, les tableaux partagés doivent être initialisés comme suit.
  Initialiser toutes les cases de COMPTEUR à 0.
  Initialiser toutes les cases de CHOIX à 0 (ou FAUX).

  Entrée en section critique[modifier]

  Le code suivant est exécuté par un fil souhaitant entrer en section critique. ID désigne son identifiant.

  Première phase[modifier]

  Cette portion de code vise à l'attribution d'un ticket. Le principe en est de regarder tous les numéros déjà attribués et de s'en attribuer un nouveau plus grand que les autres.
  ' Début de la phase d'attribution d'un ticket
  CHOIX[ID] = 1

  MAX = 0
  ' Calcul de la valeur maximale des tickets des autres threads
  POUR J = 0 À N - 1 FAIRE
  CJ = COMPTEUR[J]
  SI MAX < CJ ALORS
  MAX = CJ
  FIN SI
  FIN POUR J
  ' Attribution du nouveau ticket
  COMPTEUR [ID] = MAX + 1

  ' Fin de la phase d'attribution du ticket
  CHOIX[ID] = 0

  Cependant, il est possible que plusieurs fils d'exécution exécutent le code de cette phase de manière concurrente. Ils peuvent alors réaliser le même calcul du maximum et s'attribuer le même ticket. Ce cas est pris en compte lors de la phase suivante.
  En toute généralité, si on ne suppose pas que les lectures et écritures sont atomiques, il est même possible que des fils exécutant de manière concurrente la première phase obtiennent des valeurs de ticket différentes. Ces valeurs sont cependant nécessairement plus grandes que celles des tickets d'autres fils ayant atteint la deuxième phase avant que ne commence l'attribution des tickets pour les premiers.

  Deuxième phase[modifier]

  Cette phase correspond, dans l'analogie développée plus haut, à l'attente de son tour. Le principe est d'examiner tous les autres fils d'exécution et de tester s'ils ont un numéro de ticket inférieur, auquel cas ils doivent passer dans la section critique en premier.

  ' Boucle sur tous les fils d'exécution
  POUR J = 0 À N - 1 FAIRE
  ' On attend que le fil considéré (J) ait fini de s'attribuer un ticket.
  TANT QUE CHOIX[J] == 1 FAIRE ' (1)
  RIEN
  FIN TANT QUE
  ' On attend que le fil courant (ID) devienne plus prioritaire que le fil considéré (J).
  TANT QUE COMPTEUR[J] <> 0 ET ' (2)
  (COMPTEUR[J] < COMPTEUR[ID] OU ' (3)
  (COMPTEUR[J] == COMPTEUR[ID] ET J < ID)) ' (4)
  FAIRE
  RIEN
  FIN TANT QUE
  FIN POUR J

  La comparaison des tickets a lieu à la ligne indiquée par (3). Comme expliqué précédemment, il est possible que deux fils d'exécution s'attribuent le même ticket. Dans ce cas, le plus prioritaire est celui qui possède l'identifiant le plus petit (voir la ligne (4)). La priorité est donc évaluée selon l'ordre lexicographique sur les couples (COMPTEUR[J], J). Seuls les fils souhaitant réellement entrer en section critique sont pris en compte (voir la ligne (2)).
  Un point essentiel au bon fonctionnement de l'algorithme, et vraisemblablement l'un des plus difficiles à comprendre, est l'utilisation du tableau CHOIX, afin d'éviter de prendre en compte par erreur une ancienne valeur de ticket pour les autres fils (ligne (1)). Supposons que 2 fils J et ID, avec J < ID, exécutent la boucle de la première phase de manière concurrente et qu'ils se voient attribuer le même numéro de ticket. Pour simplifier, nous considèrerons que tous les autres fils sont hors de la section critique et ne cherchent pas à y entrer. Supposons également que, alors que le fil ID exécute la deuxième phase, le fil J n'a pas encore exécuté l'opération de mise à jour de son ticket dans la première phase (COMPTEUR [J] = MAX + 1 n'a pas été exécutée). Supposons enfin que l'on a retiré la boucle d'attente sur la valeur de CHOIX[J] (ligne (1)). Dans ce cas, le fil ID lit COMPTEUR[J] (ligne (2)), qui vaut 0, sans prendre en compte la valeur de CHOIX[J]. Cette valeur de 0 est censée signifier que le fil J est hors de la section critique et ne cherche pas à y entrer. Le fil ID en déduit qu'il est prioritaire par rapport à J et entre dans la section critique. Le fil J, poursuivant son exécution, met à jour COMPTEUR[J] et entre dans la deuxième phase. Il remarque alors qu'il a le même numéro de ticket que le fil ID et compare leurs identifiants. Comme J < ID, J est prioritaire. Il finit donc par entrer dans la section critique. Finalement, dans ce cas, les fils J et ID peuvent tous les deux entrer dans la section critique, ce que l'algorithme cherche justement à éviter.

  Sortie de la section critique[modifier]

  À la sortie de la section critique, le fil courant (ID) exécute simplement le code ci-dessous. COMPTEUR[ID] est remis à zéro, indiquant aux autres fils que le fil ID n'est plus en section critique et ne cherche pas à y accéder.
  COMPTEUR[ID] = 0

  Historique et Propriétés[modifier]

  L'algorithme de la boulangerie a été introduit par Leslie Lamport en 19741, comme une solution à un problème initialement posé et résolu par Edsger Dijkstra en 19652 (voir Algorithme d'exclusion mutuelle de Dijkstra).
  Le problème original était de fournir un algorithme réalisant l'exclusion mutuelle de N processus, utilisant uniquement des opérations atomiques simples (une seule lecture ou écriture à la fois) et vérifiant certaines propriétés :
  Symétrie de l'algorithme (aucun processus n'est favorisé a priori).

  Interblocage apparent impossible (pas de séquence du type: après vous, après vous).

  Support de processus aux vitesses d'exécution différentes.

  Un processus bloqué hors de la section critique ne doit pas empêcher d'autres processus d'y entrer.

  L'algorithme de la boulangerie est en réalité une solution à un problème plus large, puisque, en plus des propriétés précédentes, il possède les suivantes :
  Aucune famine n'est possible.

  Les opérations de lecture et d'écriture dans les tableaux partagés n'ont pas besoin d'être atomiques.

  Le premier point résulte de l'obligation pour un fil d'obtenir un nouveau ticket afin de rentrer dans la section critique une nouvelle fois. Ce nouveau ticket a nécessairement un numéro d'ordre strictement supérieur à celui de l'ancien ticket, à moins que les autres fils ne soient tous hors de la section critique et ne cherchent pas à y entrer. Un fil qui cherche à entrer dans la section critique est donc certain d'y entrer au plus tard lorsque tous les autres fils ayant exécuté la première phase de manière concurrente vis-à-vis de lui sont passés dans la section critique une seule fois.
  Le second point est particulièrement remarquable et délicat à assimiler. Il résulte de plusieurs caractéristiques de l'algorithme. En premier lieu, ce dernier n'utilise pas d'écritures concurrentes aux mêmes adresses mémoire. Le fil ID, et lui seul, peut écrire dans CHOIX[ID] et COMPTEUR[ID]. En second lieu, les valeurs de CHOIX[ID] sont booléennes, ce qui implique que n'importe quel changement de valeur est visible au travers d'un seul bit, et la modification de ce dernier est nécessairement atomique. Autrement dit, les changements de CHOIX[ID] sont perçus de manière atomique. En troisième lieu, l'utilisation de CHOIX sérialise tout accès concurrent à COMPTEUR[J], à l'exception de ceux se produisant entre fils en train d'exécuter la première phase en même temps. Dans ce dernier cas, les fils peuvent obtenir des numéros de tickets différents, mais ils attendront tous mutuellement que leurs valeurs soient visibles dans la deuxième phase, et un seul fil finira par accéder à la section critique. Par ailleurs, si un fil K était déjà dans la deuxième phase avant que les premiers ne commencent l'exécution de la première phase, alors son COMPTEUR[K] a nécessairement été lu correctement pour le calcul du maximum et les nouveaux tickets sont nécessairement supérieurs à celui-ci, même si on ne peut prévoir leur ordre relatif. Une démonstration plus formelle est disponible dans l'article original de Leslie Lamport1.
  La littérature présente parfois sous le nom d'algorithme de la boulangerie de légères variantes qui, en réalité, sont plus faibles que l'original, car elles ne vérifient pas toutes les propriétés énoncées plus haut, et notamment la dernière (lecture et écriture non nécessairement atomiques)3. La variante consistant, par exemple, à supprimer le tableau CHOIX et à le remplacer par l'utilisation d'une nouvelle valeur spéciale dans les cases de COMPTEUR, ne fonctionne pas sans opération atomique.
txt;

$this->patch = array();
}
    
    function test0() {
		$p = new LogootPosition(array(LogootId::IdMin()));
		$q = new LogootPosition(array(new LogootId(INT_MIN+1, "3", 6)));
		$lp1 = LogootPosition::getLogootPosition($p, $q, 2, "3", 7, 10);
		foreach($lp1 as $pos) {//echo $pos;
			$this->assertEquals('-1', $p->compareTo($pos));
			$this->assertEquals('1', $q->compareTo($pos));
		}
		
		$q = new LogootPosition(array(LogootId::IdMax()));
		$p = new LogootPosition(array(new LogootId(INT_MAX-1, "3", 6)));
		
		$lp2 = LogootPosition::getLogootPosition($p, $q, 2, "3", 7, 10);
		foreach($lp2 as $pos) {//echo $pos;
			$this->assertEquals('-1', $p->compareTo($pos));
			$this->assertEquals('1', $q->compareTo($pos));
		}	
		$q = new LogootPosition(array(LogootId::IdMax()));
		$p = new LogootPosition(array(new LogootId(99, "3", 3), new LogootId(99, "3", 7)));
		$lp = LogootPosition::getLogootPosition($p, $q, 2, "3", 7, 10);
		foreach($lp2 as $pos) {//echo $pos;
			$this->assertEquals('-1', $p->compareTo($pos));
			$this->assertEquals('1', $q->compareTo($pos));
		}	
	}
    
    function test1() {
        $model = manager::getNewBoModel();
        $logoot = manager::getNewEngine($model, 3);
        $this->patch[1] = $logoot->generate($this->texte1, $this->texte2);
		//echo $this->patch[1];
		$this->assertEquals($this->texte2, $model->getText());
        $this->patch[2] = $logoot->generate($this->texte2, $this->texte3);
		//echo $this->patch[2];
		$this->assertEquals($this->texte3, $model->getText());
        $this->patch[3] = $logoot->generate($this->texte3, $this->texte4);
        //echo $this->patch[3];
        $this->assertEquals(4, count($model->getPositionList()));
        $this->assertEquals(4, count($model->getLineList()));
        $this->assertEquals($this->texte4, $model->getText());
    }
     
    function test2() {
        $model  = manager::getNewBoModel();
        $logoot = manager::getNewEngine($model, 3);
        $this->patch[1] = $logoot->generate($this->texte1, $this->texte4);
		$this->assertEquals($this->texte4, $model->getText());
		$this->patch[2] = $logoot->generate($this->texte4, $this->texte5);
		//echo $this->patch[2];
		$this->assertEquals($this->texte5, $model->getText());
		$this->patch[3] = $logoot->generate($this->texte5, $this->texte6);
		//echo $this->patch[3];
		$this->assertEquals($this->texte6, $model->getText());
		$this->patch[4] = $logoot->generate($this->texte6, $this->texte7);
		//echo $this->patch[4];
		$this->assertEquals($this->texte7, $model->getText());
    }
    
    function test2b() {
        $model = manager::getNewBoModel();
        $logoot = manager::getNewEngine($model, 3);
        $logoot->generate($this->texte1, $this->texte8);
        $this->assertEquals($this->texte8, $model->getText());
  		$logoot->generate($this->texte8, $this->texte9);
		$this->assertEquals($this->texte9, $model->getText());
    }    
    
    function test3() {// Test Undo de la thèse
        $model  = manager::getNewBoModel();
        $logoot = manager::getNewEngine($model, 3);
        $this->patch[1] = $logoot->generate($this->texte1, $this->texte2);
        $this->patch[2] = $logoot->generate($this->texte2, $this->texte3);
        $this->patch[3] = $logoot->generate($this->texte3, $this->texte4);
        $this->assertEquals($this->texte4, $model->getText());
        $txt1 = $model->getText();

        $invPatch = $logoot->undoPatch($this->patch[2]);
        $logoot->integrate($invPatch);

        $invinvPatch = $logoot->undoPatch($invPatch);
        $logoot->integrate($invinvPatch);
        $txt2 = $model->getText();

        $this->assertEquals(4, count($model->getPositionList()));
        $this->assertEquals(4, count($model->getLineList()));
        $this->assertEquals($this->texte4, $model->getText());
        $this->assertEquals($txt1, $txt2);
    }

    function test4() {// Test Undo de la thèse
        $a = new LogootPlusIns(
                        new LogootPosition(
                                array(new LogootId(23, "444", 232))
                        ),
                        "A"
        );
        $b = new LogootPlusIns(
                        new LogootPosition(
                                array(new LogootId(234, "2", 2))
                        ),
                        "B"
        );
        $c = new LogootPlusIns(
                        new LogootPosition(
                                array(new LogootId(454, "3", 4))
                        ),
                        "C"
        );

        $patch01 = new LogootPatch("1" . "1", array($a, $b, $c));
        $patch02 = new LogootPatch("1" . "1", array($a, $b, $c));

        $model1 = manager::loadModel(0);
        $logoot1 = manager::getNewEngine($model1, 1); // new logootPlusEngine(NULL, 1);
        $logoot1->integrate($patch01);
        //echo $logoot1->getModel();

        $model2 = manager::loadModel(0);
        $logoot2 = manager::getNewEngine($model2, 2); // new logootPlusEngine(NULL, 2);
        $logoot2->integrate($patch02);
        //echo $logoot2->getModel();

        $delB = new LogootPlusDel(new LogootPosition(
                                array(new LogootId(234, "2", 2))
                        ),
                        "B");
        $P1 = new LogootPatch("12", array($delB));
        $logoot1->integrate($P1);
        //echo $logoot1->getModel();

        $P2 = new LogootPatch("13", array($delB));
        $logoot2->integrate($P2);
        //echo $logoot2->getModel();

        $P3 = $logoot1->undoPatch($P1);
        $logoot1->integrate($P3);
        //echo $logoot1->getModel();

        $logoot2->integrate(new LogootPatch("12", array($delB)));
        //echo $logoot2->getModel();

        $logoot1->integrate(new LogootPatch("13", array($delB)));
        $txt1 = $logoot1->getModel()->getText();
        //echo $txt1."\n";

        $logoot2->integrate($logoot1->undoPatch($P1));
        $txt2 = $logoot2->getModel()->getText();
        //echo $txt2."\n";

        $this->assertEquals($txt1, $txt2);
    }

    function test5() {
        $model = manager::loadModel(0);
        $logoot = manager::getNewEngine($model, 3);
        $logoot->compute("Space-filling_curve.xml");
        
        $doc = simplexml_load_file("Space-filling_curve.xml");
        $ota = explode("\n", $doc->current_page);
        $nta = explode("\n", $logoot->getModel()->getText());
        $counter = 0;
        if (count($ota) == 1 && $ota[0] == "")
            unset($ota[0]);
        $diffs = new Diff1($ota, $nta);
        $ok = true;
        
         foreach ($diffs->edits as $operation) {
            switch ($operation->type) {
                case "add":
                    $adds = $operation->closing;
                    ksort($adds, SORT_NUMERIC);
                    foreach ($adds as $lineNb => $linetxt) {
                        echo "Add $lineNb : '$linetxt'\n";
                    }
                    $ok = false;
                    break;
                case "delete":
                    foreach ($operation->orig as $lineNb => $linetxt) {
                        echo "Del $lineNb : '$linetxt'\n";
                    }
                    $ok = false;
                    break;
                case "change":
                    foreach ($operation->orig as $lineNb => $linetxt) {
                        echo "Change/Del $lineNb : '$linetxt'\n";
                    }
                    $adds1 = $operation->closing;
                    ksort($adds1, SORT_NUMERIC);
                    foreach ($adds1 as $lineNb => $linetxt) {
                        echo "Change/Add $lineNb : '$linetxt'\n";
                    }
                    $ok = false;
                    break;
                case "copy": break;
                default :;
            }
        }

        $this->assertTrue($ok);
    }

}

?>

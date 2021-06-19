<?php
if(php_sapi_name() !='cli') { exit('No direct script access allowed.');}
/* * * * * * * * *
 * Location Class
 * daemon/classes/Location.class.php
 * 
 * Includes Location, Zone and Point classes 
 */

 //DEPRECIATED Class not used
 class Point {
     public $x;
     public $y;

     public function __construct($x, $y) {
         $this->x = $x;
         $this->y = $y;
     }
 }

class ZONE {
    public static $m486 = "M-486 by I-74 bridge Bettendorf";
    public static $m487 = "M-487 at Bettendorf 6.5 miles below Lock 14";
    public static $m488 = "M-488 at Bettendorf 5.5 miles below Lock 14";
    public static $m489 = "M-489 at Arconic plant 4.5 miles below Lock 14";
    public static $m490 = "At marker 490 3.5 miles below Lock 14";
    public static $m491 = "At marker 491 2.5 miles below Lock 14";
    public static $m492 = "M-492 at Hampton, IL 1.5 miles below Lock 14";
    public static $m493 = "At marker 493 half mile below Lock 14";
    public static $m494 = "At marker 494 half mile above Lock 14";
    public static $m495 = "At marker 495 1.5 miles above Lock 14";
    public static $m496 = "At marker 496 2.5 miles above Lock 14";
    public static $m497 = "At marker 497 3.5 miles above Lock 14";
    public static $m498 = "At marker 498 4.5 miles above Lock 14";
    public static $m499 = "At marker 499 5.5 miles above Lock 14";
    public static $m500 = "At marker 500 6.5 miles above Lock 14";
    public static $m501 = "Marker 501 1 mile below Princeton, IA";
    public static $m502 = "Marker 502 at Princeton, IA";
    public static $m503 = "Marker 503 at Princeton, IA";
    public static $m504 = "Marker 504 at Cordova, IL";
    public static $m505 = "M-505 1 mile below QC nuke plant";
    public static $m506 = "At marker 506 by QC nuke plant";
    public static $m507 = "At marker 507 by QC nuke plant";
    public static $m508 = "M-508 1 mile above QC nuke plant";
    public static $m509 = "M-509 by 3M plant 9 miles below Clinton drawbridge";
    public static $m510 = "At mile marker 510 8 miles below Clinton drawbridge";
    public static $m511 = "M-511 at Camanche 7 miles below Clinton drawbridge";
    public static $m512 = "M-512 by Camanche marina 6 miles below Clinton drawbridge";
    public static $m513 = "M-513 at Albany, IL 5 miles below Clinton drawbridge";
    public static $m514 = "M-514 at Albany, IL 4 miles below Clinton drawbridge";
    public static $m515 = "M-515 at Vans Landing 3 miles below Clinton drawbridge";
    public static $m516 = "At marker 516 2 miles below Clinton drawbridge";
    public static $m517 = "At marker 517 1 mile below Clinton drawbridge";
    public static $mJoyce = "In Joyce Slough, Clinton's harbor";
    public static $m518 = "At marker 518, Clinton drawbridge";
    public static $m519 = "At marker 519 near Clinton Marina";
    public static $m520 = "At marker 520, Clinton's North bridge";
    public static $m521 = "At marker 521 1 mile above Clinton's North bridge";
    public static $m522 = "At marker 522 half mile below Lock 13";
    public static $m523 = "At marker 523 half mile above Lock 13";
    public static $m524 = "At marker 524 1.5 miles above Lock 13";
    public static $m525 = "At marker 525 2.5 miles above Lock 13";
    public static $m526 = "At marker 526 3.5 miles above Lock 13";
    public static $m527 = "At marker 527 4.5 miles above Lock 13";
    public static $m528 = "At marker 528 5.5 miles above Lock 13";
    public static $m529 = "At marker 529 6.5 miles above Lock 13";
    public static $m530 = "At marker 530 7.5 miles above Lock 13";
    public static $m531 = "At marker 531 8.5 miles above Lock 13";
    public static $m532 = "Mile 532, 9.5 abv Lock 13, 3 blw Sabula drawbridge";
    public static $m533 = "Mile 533, 10.5 abv Lock 13, 2 blw Sabula drawbridge";
    public static $m534 = "At marker 534 1 mile below Sabula drawbridge";
    public static $m535 = "At marker 535 Sabula drawbridge";
    public static $m536 = "At marker 536 1 mile above Sabula drawbridge";
    public static $m537 = "At Savanna, IL 2 miles above Sabula drawbridge";
    public static $m538 = "At marker 538 3 miles above Sabula drawbridge";
    public static $m539 = "At marker 539 4 miles above Sabula drawbridge";
    public static $m540 = "At marker 540 5 miles above Sabula drawbridge";

}

 class Location {
    public $live;
    public $mm;
    public $lastMM;
    public $description;
    public $point;

    public function __construct($livescan) {
        $this->live = $livescan;
        $this->mm   = "new";
        //$this->setPoint();
        //$this->calculate();
    }

    public function setPoint() {
        $this->point = [$this->live->liveLastLon, $this->live->liveLastLat];
    }

    public function calculate() {
        
        //Total pool of mile posts Savanna to Bettendorf
        $milePoints = array(
            486=>[new Point(-90.50971806363766, 41.52215220467504), new Point(-90.5092203536731,41.51372097487243)], 
            487=>[new Point(-90.48875678287305, 41.521402024002950), new Point(-90.48856266269104,41.5145424556308)],
            488=>[new Point(-90.47251555885472, 41.52437816051497), new Point(-90.47036467716465,41.51537456609466)],
            489=>[new Point(-90.45698288389242, 41.53057735758976), new Point(-90.45000250745086,41.52480546208061)],
            490=>[new Point(-90.4461928429114, 41.54182560886835), new Point(-90.43804967962095,41.53668343008653)],
            491=>[new Point(-90.43225148614556, 41.55492191671779), new Point(-90.42465891516093,41.54714647168962)],
            492=>[new Point(-90.42215634673808, 41.56423876538352), new Point(-90.41359632007243,41.55879211219473)],
            493=>[new Point(-90.40755589318907, 41.57200066107595), new Point(-90.40121765684347,41.56578132917156)],
            494=>[new Point(-90.39384285792221, 41.57842796885789), new Point(-90.38766103940617,41.57132529050489)],
            495=>[new Point(-90.37455561078977, 41.58171517893158), new Point(-90.37097459099577,41.57455780093269)],
            496=>[new Point(-90.35418070340366, 41.5875726488084), new Point(-90.34989801453619,41.58193114855811)],
            497=>[new Point(-90.34328730016247, 41.59576427084198), new Point(-90.33608085417411,41.59502112101575)],
            498=>[new Point(-90.34404272829823, 41.61119012348694), new Point(-90.33646143861851,41.6111032102589)],
            499=>[new Point(-90.3472745860646, 41.62454773858045), new Point(-90.33663122233754,41.62387063319586)],
            500=>[new Point(-90.3480736269221, 41.63971945269969), new Point(-90.33817941381621,41.63955239006518)],
            501=>[new Point(-90.34380831321272, 41.65683003496228), new Point(-90.33649979303949,41.65484790099703)],
            502=>[new Point(-90.33988256792307, 41.66828476005874), new Point(-90.3286147300638,41.66790001449647)],
            503=>[new Point(-90.33882199131011, 41.68036827724283), new Point(-90.32843393740198,41.6798418644646)],
            504=>[new Point(-90.32382303252616, 41.69122269168967), new Point(-90.31540075610307,41.68607027095535)],
            505=>[new Point(-90.31560815565506, 41.70162133249737), new Point(-90.31077421309571,41.70093421981962)],
            506=>[new Point(-90.32324160813617, 41.71865527148766), new Point(-90.3144828164786,41.71893714129034)],
            507=>[new Point(-90.32043157100178, 41.73305742526379), new Point(-90.31219715829357,41.73209034176453)],
            508=>[new Point(-90.30911551889101, 41.74805205206862), new Point(-90.30381674016407,41.74473810650169)],
            509=>[new Point(-90.29387889379554, 41.75940105234584), new Point(-90.29012440316585,41.7570342469618)],
            510=>[new Point(-90.28216840054604, 41.76853414046849), new Point(-90.27848788377898,41.76498972749543)],
            511=>[new Point(-90.2654809443937, 41.77464017600214), new Point(-90.26200151315392,41.770651247585950)],
            512=>[new Point(-90.24800719074986, 41.7843434632554), new Point(-90.24263626100766,41.77880910965498)],
            513=>[new Point(-90.23473410036074, 41.79168622191222), new Point(-90.2284317808665,41.78595112826723)],
            514=>[new Point(-90.2156953508097, 41.7973419581181), new Point(-90.21337944364016,41.79404084443492)],
            515=>[new Point(-90.19822143802581, 41.8025198609788), new Point(-90.19581674354208,41.79898355228364)],
            516=>[new Point(-90.18352536643455, 41.80932693789443), new Point(-90.17633565144088,41.80648691881999)],
            517=>[new Point(-90.18485994749022, 41.8234823269278), new Point(-90.18032482162711,41.82393548957531)],
            518=>[new Point(-90.18522602598576, 41.83743971204904), new Point(-90.18253482993897,41.83749106584514)],
            519=>[new Point(-90.17908346056349, 41.8513020234478), new Point(-90.17295527825956,41.850379130804)],
            520=>[new Point(-90.17610039282224, 41.86515500754595), new Point(-90.17058699252856,41.86429560522607)],
            521=>[new Point(-90.17297767304423, 41.87737306056449), new Point(-90.16660198044828,41.8760873927711)],
            522=>[new Point(-90.16238975538499, 41.89065244219969), new Point(-90.15871961546813,41.88892630366035)],
            523=>[new Point(-90.15857648612955, 41.9046208778465), new Point(-90.15204920435555,41.90255202787517)],
            524=>[new Point(-90.15922331108948, 41.91811211350211), new Point(-90.14839637939535,41.91635929261279)],
            525=>[new Point(-90.15792090703236, 41.92858462810474), new Point(-90.15049176877096,41.92853047111586)],
            526=>[new Point(-90.15891810761379, 41.94107477739816), new Point(-90.15487203752069,41.94093309207638)],
            527=>[new Point(-90.15826136438079, 41.95580911610016), new Point(-90.15471943137281,41.95564703478067)],
            528=>[new Point(-90.15816804572817, 41.96889364389622), new Point(-90.15019283497713,41.96669934783529)],
            529=>[new Point(-90.14371602128973, 41.9845638044717), new Point(-90.13909858199787,41.98346713433521)],
            530=>[new Point(-90.14570019987397, 41.99916325249763), new Point(-90.13663421169025,41.99881259011114)],
            531=>[new Point(-90.14783536451105, 42.01128988436283), new Point(-90.13865142581066,42.01213328702114)],
            532=>[new Point(-90.15182067613138, 42.02602246774179), new Point(-90.14629338851498,42.02665221822929)],
            533=>[new Point(-90.16063756667363, 42.03651491321578), new Point(-90.15151752534508,42.03730169372241)],
            534=>[new Point(-90.16890457045166, 42.04885717910146), new Point(-90.16066649304122,42.04930465441836)],
            535=>[new Point(-90.16873927252988, 42.06458933574678), new Point(-90.16266001168944,42.06507225175709)],
            536=>[new Point(-90.16914609409496, 42.0804515612181), new Point(-90.16249823994366,42.07970814767357)],
            537=>[new Point(-90.16729803875997, 42.09221812981502), new Point(-90.1579947493362,42.09136054497117)],
            538=>[new Point(-90.16382083849952, 42.10622273166468), new Point(-90.15894760458957,42.10600456364353)],
            539=>[new Point(-90.16773051913361, 42.11833177709393), new Point(-90.16024166340684,42.12179322620005)],
            540=>[new Point(-90.18197341024099, 42.12474496670414), new Point(-90.18304430150994,42.12795599576975)]
          );
           
          
          $polys = [
            486=>[[-90.50971806363766, 41.52215220467504],  [-90.5092203536731,41.51372097487243],  [-90.48856266269104, 41.5145424556308],   [-90.48875678287305, 41.521402024002950] ], 
            487=>[[-90.48875678287305, 41.521402024002950], [-90.48856266269104,41.5145424556308],  [-90.47036467716465, 41.51537456609466],  [-90.47251555885472, 41.52437816051497]  ],
            488=>[[-90.47251555885472, 41.52437816051497],  [-90.47036467716465,41.51537456609466], [-90.45000250745086, 41.52480546208061],  [-90.45698288389242, 41.53057735758976]  ],
            489=>[[-90.45698288389242, 41.53057735758976],  [-90.45000250745086,41.52480546208061], [-90.43804967962095, 41.53668343008653],  [-90.4461928429114,  41.54182560886835]  ],
            490=>[[-90.4461928429114,  41.54182560886835],  [-90.43804967962095,41.53668343008653], [-90.42465891516093, 41.54714647168962],  [-90.43225148614556, 41.55492191671779]  ],
            491=>[[-90.43225148614556, 41.55492191671779],  [-90.42465891516093,41.54714647168962], [-90.41359632007243, 41.55879211219473],  [-90.42215634673808, 41.56423876538352]  ],
            492=>[[-90.42215634673808, 41.56423876538352],  [-90.41359632007243,41.55879211219473], [-90.40121765684347, 41.56578132917156],  [-90.40755589318907, 41.57200066107595]  ],
            493=>[[-90.40755589318907, 41.57200066107595],  [-90.40121765684347,41.56578132917156], [-90.38766103940617, 41.57132529050489],  [-90.39384285792221, 41.57842796885789]  ],
            494=>[[-90.39384285792221, 41.57842796885789],  [-90.38766103940617,41.57132529050489], [-90.37097459099577, 41.57455780093269],  [-90.37455561078977, 41.58171517893158]  ],
            495=>[[-90.37455561078977, 41.58171517893158],  [-90.37097459099577,41.57455780093269], [-90.34989801453619, 41.58193114855811],  [-90.35418070340366, 41.5875726488084]   ],
            496=>[[-90.35418070340366, 41.5875726488084],   [-90.34989801453619,41.58193114855811], [-90.33608085417411, 41.59502112101575],  [-90.34328730016247, 41.59576427084198]  ],
            497=>[[-90.34328730016247, 41.59576427084198],  [-90.33608085417411,41.59502112101575], [-90.33646143861851, 41.6111032102589],   [-90.34404272829823, 41.61119012348694]  ],
            498=>[[-90.34404272829823, 41.61119012348694],  [-90.33646143861851,41.6111032102589],  [-90.33663122233754, 41.62387063319586],  [-90.3472745860646,  41.62454773858045]  ],
            499=>[[-90.3472745860646,  41.62454773858045],  [-90.33663122233754,41.62387063319586], [-90.33817941381621, 41.63955239006518],  [-90.3480736269221,  41.63971945269969]  ],
            500=>[[-90.3480736269221,  41.63971945269969],  [-90.33817941381621,41.63955239006518], [-90.33649979303949, 41.65484790099703],  [-90.34380831321272, 41.65683003496228]  ],
            501=>[[-90.34380831321272, 41.65683003496228],  [-90.33649979303949,41.65484790099703], [-90.3286147300638,  41.66790001449647],  [-90.33988256792307, 41.66828476005874]  ],
            502=>[[-90.33988256792307, 41.66828476005874],  [-90.3286147300638,41.66790001449647],  [-90.32843393740198, 41.6798418644646],   [-90.33882199131011, 41.68036827724283]  ],
            503=>[[-90.33882199131011, 41.68036827724283],  [-90.32843393740198,41.6798418644646],  [-90.31540075610307, 41.68607027095535],  [-90.32382303252616, 41.69122269168967]  ],
            504=>[[-90.32382303252616, 41.69122269168967],  [-90.31540075610307,41.68607027095535], [-90.31077421309571, 41.70093421981962],  [-90.31560815565506, 41.70162133249737]  ],
            505=>[[-90.31560815565506, 41.70162133249737],  [-90.31077421309571,41.70093421981962], [-90.3144828164786,  41.71893714129034],  [-90.32324160813617, 41.71865527148766]  ],
            506=>[[-90.32324160813617, 41.71865527148766],  [-90.3144828164786,41.71893714129034],  [-90.31219715829357, 41.73209034176453],  [-90.32043157100178, 41.73305742526379]  ],
            507=>[[-90.32043157100178, 41.73305742526379],  [-90.31219715829357,41.73209034176453], [-90.30381674016407, 41.74473810650169],  [-90.30911551889101, 41.74805205206862]  ],
            508=>[[-90.30911551889101, 41.74805205206862],  [-90.30381674016407,41.74473810650169], [-90.29012440316585, 41.7570342469618],   [-90.29387889379554, 41.75940105234584]  ],
            509=>[[-90.29387889379554, 41.75940105234584],  [-90.29012440316585,41.7570342469618],  [-90.27848788377898, 41.76498972749543],  [-90.28216840054604, 41.76853414046849]  ],
            510=>[[-90.28216840054604, 41.76853414046849],  [-90.27848788377898,41.76498972749543], [-90.26200151315392, 41.770651247585950],  [-90.2654809443937,  41.77464017600214]  ],
            511=>[[-90.2654809443937,  41.77464017600214],  [-90.26200151315392,41.770651247585950],  [-90.24263626100766, 41.77880910965498], [-90.24800719074986, 41.7843434632554]  ],
            512=>[[-90.24800719074986, 41.7843434632554],   [-90.24263626100766,41.77880910965498], [-90.2284317808665,  41.78595112826723],  [-90.23473410036074, 41.79168622191222], [-90.244203, 41.794008]  ],
            513=>[[-90.23473410036074, 41.79168622191222],  [-90.2284317808665,41.78595112826723],  [-90.21337944364016, 41.79404084443492],  [-90.2156953508097,  41.7973419581181]   ],
            514=>[[-90.2156953508097,  41.7973419581181],   [-90.21337944364016,41.79404084443492], [-90.19581674354208, 41.79898355228364],  [-90.19822143802581, 41.8025198609788]   ],
            515=>[[-90.19822143802581, 41.8025198609788],   [-90.19581674354208,41.79898355228364], [-90.17633565144088, 41.80648691881999],  [-90.18352536643455, 41.80932693789443]  ],
            516=>[[-90.18352536643455, 41.80932693789443],  [-90.17633565144088,41.80648691881999], [-90.18032482162711, 41.82393548957531],   [-90.18485994749022, 41.8234823269278]   ],
            517=>[[-90.208145, 41.821115],                  [-90.18032482162711,41.82393548957531], [-90.18253482993897, 41.83749106584514],  [-90.18522602598576, 41.83743971204904]  ],
            'Joyce'=> [[-90.23294839981214, 41.79808538536009], [-90.22701749141592,41.80756903332713], [-90.21559749792131, 41.81230821223932], [-90.20557127592812, 41.81802061899466], [-90.19441158230757, 41.82295207417607],
            [-90.1852557317826, 41.82723424579132], [-90.1864738578501, 41.82944337432793], [-90.19083424544831, 41.82723719598464], [-90.19632708046788, 41.82542118488396], [-90.20330171463473, 41.82315092259923], [-90.21080006723422, 
            41.81912672727466], [-90.21638017690674, 41.81568600506505], [-90.22631847832557, 41.81192101655164], [-90.23652024451495, 41.80341254671695], [-90.23852907095876, 41.79282295356613], [-90.2343452764614, 41.79288804500343], 
            [-90.23294839981214, 41.79808538536009]],
            518=>[[-90.18522602598576, 41.83743971204904],  [-90.18253482993897,41.83749106584514], [-90.17295527825956, 41.850379130804],    [-90.17908346056349, 41.8513020234478]   ],
            519=>[[-90.17908346056349, 41.8513020234478],   [-90.17295527825956,41.850379130804],   [-90.17058699252856, 41.86429560522607],  [-90.17610039282224, 41.86515500754595]  ],
            520=>[[-90.17610039282224, 41.86515500754595],  [-90.17058699252856,41.86429560522607], [-90.16660198044828, 41.8760873927711],   [-90.17297767304423, 41.87737306056449]  ],
            521=>[[-90.17297767304423, 41.87737306056449],  [-90.16660198044828,41.8760873927711],  [-90.15871961546813, 41.88892630366035],   [-90.16238975538499, 41.89065244219969]  ],
            522=>[[-90.16238975538499, 41.89065244219969],  [-90.15871961546813,41.88892630366035], [-90.15204920435555, 41.90255202787517],  [-90.15857648612955, 41.9046208778465]   ],
            523=>[[-90.15857648612955, 41.9046208778465],   [-90.15204920435555,41.90255202787517], [-90.14839637939535, 41.91635929261279],  [-90.15922331108948, 41.91811211350211]  ],
            524=>[[-90.15922331108948, 41.91811211350211],  [-90.14839637939535,41.91635929261279], [-90.15049176877096, 41.92853047111586],  [-90.15792090703236, 41.92858462810474]  ],
            525=>[[-90.15792090703236, 41.92858462810474],  [-90.15049176877096,41.92853047111586], [-90.15487203752069, 41.94093309207638],  [-90.15891810761379, 41.94107477739816]  ],
            526=>[[-90.15891810761379, 41.94107477739816],  [-90.15487203752069,41.94093309207638], [-90.15471943137281, 41.95564703478067],  [-90.15826136438079, 41.95580911610016], [-90.167271, 41.948696]  ],
            527=>[[-90.15826136438079, 41.95580911610016],  [-90.15471943137281,41.95564703478067], [-90.15019283497713, 41.96669934783529],   [-90.15816804572817, 41.96889364389622], [-90.165312, 41.962655]  ],
            528=>[[-90.15816804572817, 41.96889364389622],  [-90.15019283497713,41.96669934783529], [-90.13909858199787, 41.98346713433521],   [-90.14371602128973, 41.9845638044717]   ],
            529=>[[-90.14371602128973, 41.9845638044717],   [-90.13909858199787,41.98346713433521], [-90.13663421169025, 41.99881259011114],  [-90.14570019987397, 41.99916325249763]  ],
            530=>[[-90.14570019987397, 41.99916325249763],  [-90.13663421169025,41.99881259011114], [-90.13865142581066, 42.01213328702114],  [-90.14783536451105, 42.01128988436283]  ],
            531=>[[-90.14783536451105, 42.01128988436283],  [-90.13865142581066,42.01213328702114], [-90.14629338851498, 42.02665221822929],  [-90.15182067613138, 42.02602246774179]  ],
            532=>[[-90.15182067613138, 42.02602246774179],  [-90.14629338851498,42.02665221822929], [-90.15151752534508, 42.03730169372241],  [-90.16063756667363, 42.03651491321578]  ],
            533=>[[-90.16063756667363, 42.03651491321578],  [-90.15151752534508,42.03730169372241], [-90.16066649304122, 42.04930465441836],  [-90.16890457045166, 42.04885717910146]  ],
            534=>[[-90.16890457045166, 42.04885717910146],  [-90.16066649304122,42.04930465441836], [-90.16266001168944, 42.06507225175709],  [-90.16873927252988, 42.06458933574678]  ],
            535=>[[-90.16873927252988, 42.06458933574678],  [-90.16266001168944,42.06507225175709], [-90.16249823994366, 42.07970814767357],  [-90.16914609409496, 42.0804515612181],   ],
            536=>[[-90.16914609409496, 42.0804515612181],   [-90.16249823994366,42.07970814767357], [-90.1579947493362,  42.09136054497117],  [-90.16729803875997, 42.09221812981502]  ],
            537=>[[-90.16729803875997, 42.09221812981502],  [-90.1579947493362,42.09136054497117],  [-90.15894760458957, 42.10600456364353],  [-90.16382083849952, 42.10622273166468]  ],
            538=>[[-90.16382083849952, 42.10622273166468],  [-90.15894760458957,42.10600456364353], [-90.16024166340684, 42.12179322620005],  [-90.16773051913361, 42.11833177709393]  ],
            539=>[[-90.16773051913361, 42.11833177709393],  [-90.16024166340684,42.12179322620005], [-90.18304430150994, 42.12795599576975],  [-90.18197341024099, 42.12474496670414]  ],
            ];
               
        //Range of miles posts used by this app
        if($this->live->liveDirection=="undetermined") {
            echo "Location::calculate() halted because direction undetermined yet.\r\n";
            return;
        }


        $urange = [495,496,497,498,499,500,501,502,503,504,505,506,507,508,509,510,511,512,513,514,515,516,517, 'Joyce', 518,519,520,521,522,523,524,525,526,527,528,529,530,531,532,533,534,535,536,537,538,539];
        $drange = [539,538,537,536,535,534,533,532,531,530,529,528,527,526,525,524,523,522,521,520,519,518,'Joyce', 517,516,515,514,513,512,511,510,509,508,507,506,505,504,503,502,501,500,499,498,497,496,495];
        $range  = $this->live->liveDirection=="upriver" ? $urange : $drange;
        $this->setPoint();
        foreach($range as $m) {
            $inside = $this->insidePoly($this->point, $polys[$m]);
            if($inside && $this->live->liveDirection=="upriver") {
                echo "Location::calculate() found ".$m." for ".$this->live->liveName."\n";
                $this->lastMM = $this->mm; //Save last marker before updating
                $this->mm = $m;
                $mileMarker = "m".$m;
                $this->description = ZONE::$$mileMarker;
                break;
            } 

            if($inside && $this->live->liveDirection=="downriver") {
                $um = ($m + 1); //To reflect that polygon entry was at upper mile line
                echo "Location::calculate() found ".$um." for ".$this->live->liveName."\n";
                $this->lastMM = $this->mm; //Save last marker before updating
                $this->mm = $m;
                $mileMarker = "m".$um;
                $this->description = ZONE::$$mileMarker;
                break;
            } 
        }
        if($inside==false) {
            echo "Location search ended at $m for ".$this->live->liveName."\r\n";
        }
        
    }

    public function lineside($a, $b, $p) {
        $p1 = new Point(($p->x - $a->x), ($p->y - $a->y));
        $b1 = new Point(($b->x - $a->x), ($b->y - $a->y));
        $c  = (($p1->x * $b1->y)-($p1->y * $b1->x));
        return $c<0 ? "left" : "right";
    }

    public function insidePoly($point, $vs) {
        // ray-casting algorithm based on
        // http://www.ecse.rpi.edu/Homepages/wrf/Research/Short_Notes/pnpoly.html
    
        $x = $point[0]; $y = $point[1];
        $len = count($vs);
        $inside = false;
        for ($i = 0, $j = $len - 1; $i < $len; $j = $i++) {
            $xi = $vs[$i][0]; $yi = $vs[$i][1];
            $xj = $vs[$j][0]; $yj = $vs[$j][1];
    
            $intersect = (($yi > $y) != ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);
            if ($intersect) { $inside = !$inside; }
        }
    
        return $inside;
    }
 }
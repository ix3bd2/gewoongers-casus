<?php

namespace App\Http\Controllers;

class ProductionStateController extends Controller
{
    public function index(): array
    {
        $json = '[
            {
                "id": 123,
                "saw": {
                    "liggerg40": {
                        "title": "Ligger G40",
                        "amount": 2,
                        "value": 1600
                    },
                    "staanderg54g56taatsdeur": {
                        "title": "Staander G54 + G56 taatsdeur",
                        "amount": 2,
                        "value": 2396
                    },
                    "profielkleur": {
                        "title": "PROFIELKLEUR: RAL 7032 Kiezel grijs"
                    }
                }
            },
            {
                "id": 789,
                "saw": {
                    "staandersg70verstekdeur": {
                        "title": "Staanders G70 verstek deur",
                        "amount": 2,
                        "value": 2785
                    },
                    "staanderg62g69deur": {
                        "title": "Staander G69 deur",
                        "amount": 1,
                        "value": 2785
                    },
                    "liggerg40": {
                        "title": "Ligger G40",
                        "amount": 2,
                        "value": 1600
                    },
                    "profielkleur": {
                        "title": "PROFIELKLEUR: Leem"
                    }
                }
            }
        ]';

        $data = json_decode($json, true);
        $newJSON = [];

        foreach ($data as $entry) { //foreach element in $arr
            if (isset($entry['saw']['profielkleur'])) {
                $profilekleur = $entry['saw']['profielkleur']['title'];
                foreach ($entry['saw'] as $key => $sawEntry) {
                    if (isset($sawEntry['amount'])) {
                        preg_match_all('~[0-9]+~', $key, $matches);
                        foreach ($matches as $number) {
                            foreach ($number as $Gnumber) {
                                $newJSON[$profilekleur][] = array('G' . $Gnumber => array('length'=> $sawEntry['value'],
                                'count'=> $sawEntry['amount'],
                            ));
                            }
                        }
                    }
                }
            }
        }
        return $newJSON;
    }
}

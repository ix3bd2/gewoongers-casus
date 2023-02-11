<?php

namespace App\Http\Controllers;

class ProductionStateController extends Controller
{
    public function index(): array
    {
        // Get the contents of the file located at storage/data/ProductieStaat.json
        $json = file_get_contents(storage_path() . "/data/ProductieStaat.json");

        // Decode the JSON data into an associative array
        $data = json_decode($json, true);
        $newJSON = [];

        // Loop through each entry in the $data array
        foreach ($data as $entry) {
            // Check if the 'saw' and 'profielkleur' keys are present in the entry
            if (isset($entry['saw']['profielkleur'])) {
                // Store the value of the 'title' key in the 'profielkleur' variable
                $profilekleur = $entry['saw']['profielkleur']['title'];
                // Loop through each value in the 'saw' key of the entry
                foreach ($entry['saw'] as $key => $sawEntry) {
                    // Check if the 'amount' key is present in the current value
                    if (isset($sawEntry['amount'])) {
                        // Use a regular expression to extract any numbers from the current key
                        preg_match_all('~[0-9]+~', $key, $matches);
                        // Loop through the matches
                        foreach ($matches as $number) {
                            // Loop through each match
                            foreach ($number as $Gnumber) {
                                // Add the extracted information to the new JSON array
                                $newJSON[$profilekleur][] = array('G' . $Gnumber => array(
                                    'length' => $sawEntry['value'],
                                    'count' => $sawEntry['amount'],
                                ));
                            }
                        }
                    }
                }
            }
        }
        // Return the new JSON array
        return $newJSON;
    }
}

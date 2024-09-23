<?php

namespace App\Services;

use App\Interfaces\IHyphenationService;

class HyphenationService implements IHyphenationService {

    private String $wordToHyphenate;
    private array $syllableArray;
    private array $doubledIndexWordArray;
    private array $patternWithNumbersArray;
    private array $doubledIndexPatternArray;
    private array $selectedSyllableArray;
    private array $finalWordArray;
    private string $finalProcessedWord;

    public function __construct(String $wordToHyphenate, array $hyphenArray)
    {
        $this->syllableArray = $hyphenArray;
        $this->wordToHyphenate = $wordToHyphenate;
        $this->finalProcessedWord = '';
        $this->selectedSyllableArray = [];
        $this->finalWordArray = [];
        $this->doubledIndexWordArray = [];
        $this->patternWithNumbersArray = [];
        $this->doubledIndexPatternArray = [];
    }

    public function HyphenateWord() : void {
        self::FindUsableSyllables();
        self::FindSyllablePositionsInWord();
        self::MergeSyllablesAndWordPositionArrays();
        self::FinalProcessing();
    }

    private function FindUsableSyllables() : void {
        $arrayWithoutNumbers = $this->FilterOutNumbersFromArray($this->syllableArray);

        foreach ($arrayWithoutNumbers as $key => $syllable) {
            if ($this->IsFirstSyllable($syllable) && str_starts_with($this->wordToHyphenate, substr($syllable, 1))) {
                $this->selectedSyllableArray[$key] = $syllable;
            }
            if (!$this->IsFirstSyllable($syllable) && !$this->IsLastSyllable($syllable) && str_contains($this->wordToHyphenate, $syllable)) {
                $this->selectedSyllableArray[$key] = $syllable;
            }
            if ($this->IsLastSyllable($syllable) && str_ends_with($this->wordToHyphenate, substr($syllable, 0, -1))) {
                $this->selectedSyllableArray[$key] = $syllable;
            }
        }

        foreach ($this->selectedSyllableArray as $key => $value) {
            $this->patternWithNumbersArray[$key] = $this->syllableArray[$key];
        }
    }

    private function FindSyllablePositionsInWord() : void {
        $this->wordToHyphenate = "." . $this->wordToHyphenate . ".";
        $wordCharacterArray = str_split($this->wordToHyphenate);

        foreach ($this->selectedSyllableArray as $key => $pattern) {
            $patternWithoutNumbers = str_split($this->RemoveNumbersFromString($pattern));
            $fullPatternChars = str_split(str_replace("\n","",$this->patternWithNumbersArray[$key]));

            $successfulMatchCount = 0;
            $comparisonBuffer = 0;
            $currentIndex = 0;

            $patternPositions = [];

            while ($successfulMatchCount < count($patternWithoutNumbers)) {
                if ($wordCharacterArray[$comparisonBuffer + $currentIndex] !== $patternWithoutNumbers[$currentIndex]) {
                    $successfulMatchCount = 0;
                    $patternPositions = [];
                }

                if($wordCharacterArray[$comparisonBuffer + $currentIndex] === $patternWithoutNumbers[$currentIndex]){
                    $successfulMatchCount++;
                    $patternPositions[$comparisonBuffer + $currentIndex] = $patternWithoutNumbers[$currentIndex];
                }

                $currentIndex++;

                if ($currentIndex >= count($patternWithoutNumbers)) {
                    $comparisonBuffer++;
                    $currentIndex = 0;
                }

                if ($successfulMatchCount === count($patternWithoutNumbers)) {
                    self::BuildWordWithNumbers($patternPositions, $fullPatternChars);
                    break;
                }
            }
        }
    }

    private function MergeSyllablesAndWordPositionArrays() : void {
        ksort($this->finalWordArray);
        ksort($this->doubledIndexWordArray);
        
        $wordToHyphenateSplit = str_split($this->wordToHyphenate);
        $wordToHyphenateExpandedArray = [];

        foreach ($wordToHyphenateSplit as $key => $wordToHyphenateChar) {
            $wordToHyphenateExpandedArray[$key * 2] = $wordToHyphenateChar;
        }

        $this->finalWordArray = $this->finalWordArray + $wordToHyphenateExpandedArray;
        ksort($this->finalWordArray);
    }

    private function BuildWordWithNumbers(Array $patternWithCharPositions, Array $fullPattern) : void {

        $this->doubledIndexPatternArray = [];

        foreach ($patternWithCharPositions as $key => $patternCharNoNumber) {
            $this->doubledIndexPatternArray[$key * 2] = $patternCharNoNumber;
            $this->doubledIndexWordArray[$key * 2] = $patternCharNoNumber;
        }

        $iterationDoubleKey = array_key_first($this->doubledIndexPatternArray);

        for ($i = 0; $i < count($fullPattern); $i++) {
            if (is_numeric($fullPattern[$i])) {
                $this->doubledIndexPatternArray[$iterationDoubleKey - 1] = $fullPattern[$i];
                continue;
            }

            $iterationDoubleKey = $iterationDoubleKey + 2;
        }

        ksort($this->doubledIndexPatternArray);

        foreach ($this->doubledIndexPatternArray as $key => $value) {
            if (!isset($this->finalWordArray[$key])){
                $this->finalWordArray[$key] = $value;
            }

            if (isset($this->finalWordArray[$key]) && is_numeric($value) && is_numeric($this->finalWordArray[$key])){
                $this->finalWordArray[$key] = max($value, $this->finalWordArray[$key]);
            }
        }
    }

    private function FilterOutNumbersFromArray($arrayToFilter) : array {
        foreach ($arrayToFilter as $key => $wordToFilter){
            $arrayToFilter[$key] = self::RemoveNumbersFromString($wordToFilter);
        }

        return $arrayToFilter;
    }

    private function RemoveNumbersFromString(String $word) : String {
        return preg_replace("/[^a-zA-Z.]/", "", $word);
    }

    private function IsFirstSyllable(String $word) : bool {
        if ((strpos($word, ".") === 0)) {
            return true;
        }
        return false;
    }

    private function IsLastSyllable(String $word) : bool {
        if (str_ends_with($word, ".")) {
            return true;
        }
        return false;
    }

    private function FinalProcessing() : void {
        foreach ($this->finalWordArray as $key => $value) {
            if (is_numeric($value)) {
                if ($value % 2 === 0) {
                    $this->finalWordArray[$key] = " ";
                }
                else {
                    $this->finalWordArray[$key] = "-";
                }
            }
        }

        $this->finalProcessedWord = implode($this->finalWordArray);
    }

    public function GetFinalWord() : string{
        return $this->finalProcessedWord;
    }
}
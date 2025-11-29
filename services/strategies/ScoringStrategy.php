<?php
/**
 * ScoringStrategy Interface
 * 
 * Defines the contract for all questionnaire scoring strategies.
 * Each questionnaire type (GDS-15, PHQ-9, GAD-7, etc.) implements this interface
 * with its own validated clinical scoring algorithm.
 */

interface ScoringStrategy {
    /**
     * Calculate total score from user responses
     * 
     * @param array $responses User's responses to questions
     * @return int Total score
     */
    public function calculateScore(array $responses): int;
    
    /**
     * Interpret the score based on clinical thresholds
     * 
     * @param int $score Total score
     * @return array Interpretation with level, color, emoji, message, recommendation
     */
    public function interpret(int $score): array;
    
    /**
     * Get maximum possible score for this questionnaire
     * 
     * @return int Maximum score
     */
    public function getMaxScore(): int;
    
    /**
     * Get the name of this questionnaire
     * 
     * @return string Questionnaire name
     */
    public function getName(): string;
    
    /**
     * Get the clinical reference/source
     * 
     * @return string Clinical reference
     */
    public function getReference(): string;
}

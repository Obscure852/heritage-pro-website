<?php

namespace App\Services\Messaging;

use App\Helpers\TermHelper;
use Illuminate\Support\Facades\Log;

/**
 * SmsPlaceholderService
 *
 * Handles replacement of placeholders in SMS messages with actual values.
 */
class SmsPlaceholderService
{
    /**
     * Available placeholders and their descriptions
     */
    public const PLACEHOLDERS = [
        '{student_name}' => 'Student full name',
        '{student_first_name}' => 'Student first name',
        '{student_last_name}' => 'Student last name',
        '{sponsor_name}' => 'Sponsor/Parent name',
        '{school_name}' => 'School name',
        '{class_name}' => 'Class/Grade name',
        '{term_name}' => 'Current term name',
        '{date}' => 'Current date',
        '{time}' => 'Current time',
        '{balance}' => 'Account balance',
        '{amount}' => 'Amount',
    ];

    /**
     * Replace placeholders in a message with actual values
     *
     * @param string $message The message template with placeholders
     * @param array $context Context data for replacement (recipient info, etc.)
     * @return string The message with placeholders replaced
     */
    public function replacePlaceholders(string $message, array $context = []): string
    {
        // Get school info
        $schoolName = settings('school.name', config('app.name', 'School'));

        // Get current term
        $currentTerm = TermHelper::getCurrentTerm();
        $termName = $currentTerm ? $currentTerm->name : '';

        // Build replacement map
        $replacements = [
            '{school_name}' => $schoolName,
            '{term_name}' => $termName,
            '{date}' => now()->format('d M Y'),
            '{time}' => now()->format('H:i'),
        ];

        // Add context-specific replacements
        if (!empty($context['sponsor_name'])) {
            $replacements['{sponsor_name}'] = $context['sponsor_name'];
        }

        if (!empty($context['student_name'])) {
            $replacements['{student_name}'] = $context['student_name'];
        }

        if (!empty($context['student_first_name'])) {
            $replacements['{student_first_name}'] = $context['student_first_name'];
        }

        if (!empty($context['student_last_name'])) {
            $replacements['{student_last_name}'] = $context['student_last_name'];
        }

        if (!empty($context['class_name'])) {
            $replacements['{class_name}'] = $context['class_name'];
        }

        if (!empty($context['balance'])) {
            $replacements['{balance}'] = number_format($context['balance'], 2);
        }

        if (!empty($context['amount'])) {
            $replacements['{amount}'] = number_format($context['amount'], 2);
        }

        // Perform replacements
        $processedMessage = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $message
        );

        // Remove any remaining unreplaced placeholders (optional - keeps them if no data)
        // $processedMessage = preg_replace('/\{[^}]+\}/', '', $processedMessage);

        return $processedMessage;
    }

    /**
     * Build context array from sponsor data
     *
     * @param mixed $sponsor The sponsor model or array
     * @return array Context array for placeholder replacement
     */
    public function buildSponsorContext($sponsor): array
    {
        $context = [];

        if (is_array($sponsor)) {
            $context['sponsor_name'] = $sponsor['name'] ?? $sponsor['sponsor_name'] ?? '';

            // If sponsor has associated student
            if (!empty($sponsor['student'])) {
                $student = $sponsor['student'];
                $context['student_name'] = $student['name'] ?? trim(($student['firstname'] ?? '') . ' ' . ($student['lastname'] ?? ''));
                $context['student_first_name'] = $student['firstname'] ?? '';
                $context['student_last_name'] = $student['lastname'] ?? '';
                $context['class_name'] = $student['grade']['name'] ?? $student['class_name'] ?? '';
            }

            if (!empty($sponsor['balance'])) {
                $context['balance'] = $sponsor['balance'];
            }
        } elseif (is_object($sponsor)) {
            $context['sponsor_name'] = $sponsor->name ?? $sponsor->sponsor_name ?? '';

            // If sponsor has associated students
            if (method_exists($sponsor, 'students') || isset($sponsor->students)) {
                $students = $sponsor->students ?? collect();
                if ($students->isNotEmpty()) {
                    $student = $students->first();
                    $context['student_name'] = $student->name ?? trim($student->firstname . ' ' . $student->lastname);
                    $context['student_first_name'] = $student->firstname ?? '';
                    $context['student_last_name'] = $student->lastname ?? '';
                    $context['class_name'] = $student->grade->name ?? '';
                }
            }

            if (isset($sponsor->balance)) {
                $context['balance'] = $sponsor->balance;
            }
        }

        return $context;
    }

    /**
     * Build context array from user data
     *
     * @param mixed $user The user model or array
     * @return array Context array for placeholder replacement
     */
    public function buildUserContext($user): array
    {
        $context = [];

        if (is_array($user)) {
            $context['sponsor_name'] = $user['name'] ?? trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
        } elseif (is_object($user)) {
            $context['sponsor_name'] = $user->name ?? trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
        }

        return $context;
    }

    /**
     * Check if a message contains placeholders
     *
     * @param string $message The message to check
     * @return bool True if message contains placeholders
     */
    public function hasPlaceholders(string $message): bool
    {
        return preg_match('/\{[^}]+\}/', $message) === 1;
    }

    /**
     * Extract placeholders from a message
     *
     * @param string $message The message to extract from
     * @return array List of placeholders found
     */
    public function extractPlaceholders(string $message): array
    {
        preg_match_all('/\{[^}]+\}/', $message, $matches);
        return $matches[0] ?? [];
    }
}

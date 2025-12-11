<?php

namespace Database\Seeders;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Seeder;

class SupportTicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a sample user (first regular user, not admin)
        $user = User::whereHas('roles', function ($query) {
            $query->where('name', 'user');
        })->first();

        if (! $user) {
            $this->command->warn('⚠️ No regular user found. Please create a user first.');

            return;
        }

        $tickets = [
            // Visa & Immigration
            [
                'user_id' => $user->id,
                'subject' => 'Visa Application Status Update',
                'message' => 'Hello, I submitted my USA B1/B2 visa application 2 weeks ago and haven\'t received any updates. Could you please check the status? My application reference is APP-2024-12345.',
                'category' => 'visa',
                'priority' => 'high',
                'status' => 'open',
            ],
            [
                'user_id' => $user->id,
                'subject' => 'Document Requirement Clarification for UK Visa',
                'message' => 'I\'m preparing documents for UK Tier 4 Student visa. Do I need to submit original bank statements or certified copies? Also, how recent should the financial documents be?',
                'category' => 'visa',
                'priority' => 'normal',
                'status' => 'in_progress',
            ],

            // Jobs & Applications
            [
                'user_id' => $user->id,
                'subject' => 'Job Application Not Showing',
                'message' => 'I applied for the Software Engineer position at ABC Company yesterday, but I can\'t see it in my applications list. Can you help me verify if my application was submitted successfully?',
                'category' => 'jobs',
                'priority' => 'normal',
                'status' => 'open',
            ],
            [
                'user_id' => $user->id,
                'subject' => 'CV Review Request',
                'message' => 'I\'ve uploaded my CV to the platform but haven\'t received any interviews yet. Could someone from your team review my CV and provide feedback on how to improve it?',
                'category' => 'jobs',
                'priority' => 'low',
                'status' => 'resolved',
            ],

            // Account & Profile
            [
                'user_id' => $user->id,
                'subject' => 'Unable to Update Phone Number',
                'message' => 'I\'m trying to update my phone number in my profile settings but keep getting an error message "Invalid format". I\'m entering a valid Bangladesh number in the format 01712345678.',
                'category' => 'account',
                'priority' => 'normal',
                'status' => 'open',
            ],
            [
                'user_id' => $user->id,
                'subject' => 'Two-Factor Authentication Setup Issue',
                'message' => 'I want to enable 2FA for my account security, but I don\'t see the option in my security settings. Is this feature available for all users?',
                'category' => 'account',
                'priority' => 'low',
                'status' => 'closed',
            ],

            // Payment & Wallet
            [
                'user_id' => $user->id,
                'subject' => 'Payment Failed But Money Deducted',
                'message' => 'I tried to pay for visa consultancy service using bKash. The payment failed on your website, but ৳5,000 was deducted from my bKash account. Transaction ID: TRX123456789. Please refund urgently.',
                'category' => 'payment',
                'priority' => 'urgent',
                'status' => 'in_progress',
            ],
            [
                'user_id' => $user->id,
                'subject' => 'Referral Reward Not Credited',
                'message' => 'I referred my friend who successfully registered and completed his profile 3 days ago, but I haven\'t received my referral reward in my wallet yet. My referral code is REF12345.',
                'category' => 'payment',
                'priority' => 'normal',
                'status' => 'open',
            ],

            // Services & Booking
            [
                'user_id' => $user->id,
                'subject' => 'Consultation Appointment Cancellation',
                'message' => 'I need to cancel my visa consultation appointment scheduled for December 15, 2025 at 10:00 AM due to a personal emergency. Can I reschedule it to next week?',
                'category' => 'services',
                'priority' => 'high',
                'status' => 'open',
            ],
            [
                'user_id' => $user->id,
                'subject' => 'Service Pricing Information',
                'message' => 'Could you provide detailed pricing for Canada Express Entry PR processing? Does the fee include document translation and attestation, or are those charged separately?',
                'category' => 'services',
                'priority' => 'low',
                'status' => 'resolved',
            ],

            // Technical Support
            [
                'user_id' => $user->id,
                'subject' => 'File Upload Not Working',
                'message' => 'I\'m trying to upload my passport scan in profile section but getting "Upload failed" error every time. I\'ve tried both PDF and JPG formats, both under 5MB. Using Chrome browser on Windows 11.',
                'category' => 'technical',
                'priority' => 'high',
                'status' => 'open',
            ],
            [
                'user_id' => $user->id,
                'subject' => 'Mobile App Performance Issues',
                'message' => 'The website is very slow on my mobile phone. Pages take 10-15 seconds to load. I\'m using Samsung Galaxy S21 with 4G connection. Desktop version works fine.',
                'category' => 'technical',
                'priority' => 'normal',
                'status' => 'in_progress',
            ],
        ];

        foreach ($tickets as $ticketData) {
            SupportTicket::create($ticketData);
        }

        $this->command->info('✅ Support tickets seeded successfully!');
        $this->command->info('📊 Created '.count($tickets).' sample tickets across 6 categories');
    }
}

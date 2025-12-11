<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create FAQ Categories
        $categories = [
            [
                'name' => 'Visa & Immigration',
                'slug' => 'visa',
                'description' => 'Questions about visa applications, immigration processes, and document requirements',
                'icon' => '🛂',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Jobs & Applications',
                'slug' => 'jobs',
                'description' => 'Job search, application process, and interview tips',
                'icon' => '💼',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Account & Profile',
                'slug' => 'account',
                'description' => 'Account setup, profile completion, and security',
                'icon' => '👤',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Payment & Wallet',
                'slug' => 'payment',
                'description' => 'Payment methods, wallet recharge, and refund policy',
                'icon' => '💳',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Services & Booking',
                'slug' => 'services',
                'description' => 'Service selection, booking, and appointment management',
                'icon' => '🎯',
                'order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Technical Support',
                'slug' => 'technical',
                'description' => 'Website issues, login problems, and technical help',
                'icon' => '🔧',
                'order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $categoryData) {
            FaqCategory::create($categoryData);
        }

        // Get created categories
        $visaCategory = FaqCategory::where('slug', 'visa')->first();
        $jobsCategory = FaqCategory::where('slug', 'jobs')->first();
        $accountCategory = FaqCategory::where('slug', 'account')->first();
        $paymentCategory = FaqCategory::where('slug', 'payment')->first();
        $servicesCategory = FaqCategory::where('slug', 'services')->first();
        $technicalCategory = FaqCategory::where('slug', 'technical')->first();

        // Visa & Immigration FAQs
        $visaFaqs = [
            [
                'question' => 'What documents do I need for a tourist visa application?',
                'answer' => '<p>For a tourist visa application, you typically need:</p>
                    <ul>
                        <li>Valid passport (minimum 6 months validity)</li>
                        <li>Completed visa application form</li>
                        <li>Recent passport-sized photographs</li>
                        <li>Proof of accommodation (hotel bookings)</li>
                        <li>Return flight tickets</li>
                        <li>Bank statements (last 3-6 months)</li>
                        <li>Travel insurance</li>
                        <li>Proof of employment or business</li>
                    </ul>
                    <p>Specific requirements may vary by country. Check the embassy website for detailed requirements.</p>',
                'order' => 1,
            ],
            [
                'question' => 'How long does the visa processing take?',
                'answer' => '<p>Visa processing times vary by country and visa type:</p>
                    <ul>
                        <li><strong>Tourist Visa:</strong> 5-15 working days</li>
                        <li><strong>Student Visa:</strong> 4-8 weeks</li>
                        <li><strong>Work Visa:</strong> 6-12 weeks</li>
                        <li><strong>Business Visa:</strong> 3-10 working days</li>
                    </ul>
                    <p>We recommend applying at least 1-2 months before your planned travel date.</p>',
                'order' => 2,
            ],
            [
                'question' => 'Can I track my visa application status?',
                'answer' => '<p>Yes! You can track your visa application status through:</p>
                    <ol>
                        <li>Login to your BideshGomon account</li>
                        <li>Go to "My Applications"</li>
                        <li>View real-time status updates</li>
                        <li>Receive email notifications for status changes</li>
                    </ol>
                    <p>You can also track directly on the embassy website using your application reference number.</p>',
                'order' => 3,
            ],
            [
                'question' => 'What if my visa application is rejected?',
                'answer' => '<p>If your visa application is rejected:</p>
                    <ul>
                        <li>Review the rejection letter carefully</li>
                        <li>Understand the reason for rejection</li>
                        <li>Address the issues mentioned</li>
                        <li>Reapply after fixing the problems</li>
                        <li>Consider consulting with our visa experts</li>
                    </ul>
                    <p>Most rejections are due to incomplete documentation or insufficient proof of funds.</p>',
                'order' => 4,
            ],
        ];

        foreach ($visaFaqs as $faq) {
            Faq::create(array_merge($faq, [
                'faq_category_id' => $visaCategory->id,
                'is_published' => true,
            ]));
        }

        // Jobs & Applications FAQs
        $jobsFaqs = [
            [
                'question' => 'How do I apply for jobs on BideshGomon?',
                'answer' => '<p>To apply for jobs:</p>
                    <ol>
                        <li>Complete your profile with education, work experience, and skills</li>
                        <li>Upload your CV/Resume</li>
                        <li>Browse available jobs in your field</li>
                        <li>Click "Apply Now" on your preferred job</li>
                        <li>Submit your application with a cover letter</li>
                    </ol>
                    <p>Employers will review your profile and contact you if shortlisted.</p>',
                'order' => 1,
            ],
            [
                'question' => 'Is there a fee for job applications?',
                'answer' => '<p>No, job applications on BideshGomon are completely <strong>FREE</strong>.</p>
                    <p>However, some premium services like CV writing, interview coaching, and profile highlighting may have charges.</p>',
                'order' => 2,
            ],
            [
                'question' => 'How can I improve my chances of getting hired?',
                'answer' => '<p>To improve your job prospects:</p>
                    <ul>
                        <li>Complete your profile 100% with all details</li>
                        <li>Upload a professional CV with relevant experience</li>
                        <li>Add a professional photo</li>
                        <li>Get skill certifications and add them to your profile</li>
                        <li>Apply to jobs that match your qualifications</li>
                        <li>Write personalized cover letters for each application</li>
                        <li>Update your profile regularly</li>
                    </ul>',
                'order' => 3,
            ],
        ];

        foreach ($jobsFaqs as $faq) {
            Faq::create(array_merge($faq, [
                'faq_category_id' => $jobsCategory->id,
                'is_published' => true,
            ]));
        }

        // Account & Profile FAQs
        $accountFaqs = [
            [
                'question' => 'How do I create an account?',
                'answer' => '<p>Creating an account is easy:</p>
                    <ol>
                        <li>Click "Register" on the homepage</li>
                        <li>Enter your name, email, and password</li>
                        <li>Verify your email address</li>
                        <li>Complete your profile with basic information</li>
                    </ol>
                    <p>You can also register using Google OAuth for quick signup.</p>',
                'order' => 1,
            ],
            [
                'question' => 'I forgot my password. How do I reset it?',
                'answer' => '<p>To reset your password:</p>
                    <ol>
                        <li>Go to the login page</li>
                        <li>Click "Forgot Password?"</li>
                        <li>Enter your registered email address</li>
                        <li>Check your email for a password reset link</li>
                        <li>Click the link and set a new password</li>
                    </ol>
                    <p>If you don\'t receive the email, check your spam folder or contact support.</p>',
                'order' => 2,
            ],
            [
                'question' => 'How do I complete my profile?',
                'answer' => '<p>Complete your profile by adding:</p>
                    <ul>
                        <li><strong>Personal Information:</strong> Name, date of birth, nationality, address</li>
                        <li><strong>Contact Details:</strong> Phone numbers, email addresses</li>
                        <li><strong>Passport Information:</strong> Passport number, expiry date, scan copies</li>
                        <li><strong>Education:</strong> Degrees, certificates, institutions</li>
                        <li><strong>Work Experience:</strong> Previous jobs, responsibilities, duration</li>
                        <li><strong>Skills & Languages:</strong> Professional skills, language proficiency</li>
                    </ul>
                    <p>A complete profile increases your chances of getting hired and faster visa processing.</p>',
                'order' => 3,
            ],
        ];

        foreach ($accountFaqs as $faq) {
            Faq::create(array_merge($faq, [
                'faq_category_id' => $accountCategory->id,
                'is_published' => true,
            ]));
        }

        // Payment & Wallet FAQs
        $paymentFaqs = [
            [
                'question' => 'What payment methods do you accept?',
                'answer' => '<p>We accept multiple payment methods:</p>
                    <ul>
                        <li><strong>Mobile Banking:</strong> bKash, Nagad, Rocket</li>
                        <li><strong>Cards:</strong> Visa, Mastercard, Amex</li>
                        <li><strong>Bank Transfer:</strong> Direct bank deposit</li>
                        <li><strong>Wallet:</strong> Use your BideshGomon wallet balance</li>
                    </ul>
                    <p>All payments are secured with SSL encryption.</p>',
                'order' => 1,
            ],
            [
                'question' => 'How does the wallet work?',
                'answer' => '<p>The BideshGomon Wallet allows you to:</p>
                    <ul>
                        <li>Add money using bKash, Nagad, or bank transfer</li>
                        <li>Pay for services instantly without entering payment details</li>
                        <li>Receive referral rewards and cashback</li>
                        <li>Track all transactions in one place</li>
                        <li>Request withdrawals to your bank account</li>
                    </ul>
                    <p>Wallet balance never expires and is 100% secure.</p>',
                'order' => 2,
            ],
            [
                'question' => 'What is your refund policy?',
                'answer' => '<p>Our refund policy:</p>
                    <ul>
                        <li>Full refund if service is not delivered within promised time</li>
                        <li>Partial refund for incomplete or unsatisfactory services</li>
                        <li>Refund requests must be made within 7 days of service</li>
                        <li>Refunds are processed within 5-10 working days</li>
                        <li>Amount is refunded to original payment method or wallet</li>
                    </ul>
                    <p>For more details, check our <a href="/legal/refund-policy" class="text-indigo-600 hover:underline">Refund Policy</a> page.</p>',
                'order' => 3,
            ],
        ];

        foreach ($paymentFaqs as $faq) {
            Faq::create(array_merge($faq, [
                'faq_category_id' => $paymentCategory->id,
                'is_published' => true,
            ]));
        }

        // Services & Booking FAQs
        $servicesFaqs = [
            [
                'question' => 'What services does BideshGomon offer?',
                'answer' => '<p>We offer comprehensive services including:</p>
                    <ul>
                        <li><strong>Visa Services:</strong> Tourist, student, work, business visas</li>
                        <li><strong>Job Placement:</strong> Local and international job opportunities</li>
                        <li><strong>Education:</strong> University admission, course guidance</li>
                        <li><strong>Travel:</strong> Flight booking, hotel reservation, tour packages</li>
                        <li><strong>Document Services:</strong> Translation, attestation, courier</li>
                        <li><strong>Consultancy:</strong> Expert guidance for migration</li>
                    </ul>',
                'order' => 1,
            ],
            [
                'question' => 'How do I book a service?',
                'answer' => '<p>Booking a service is simple:</p>
                    <ol>
                        <li>Browse our services directory</li>
                        <li>Select the service you need</li>
                        <li>Fill out the application form</li>
                        <li>Upload required documents</li>
                        <li>Make the payment</li>
                        <li>Track your application status</li>
                    </ol>
                    <p>Our team will contact you within 24 hours after booking.</p>',
                'order' => 2,
            ],
        ];

        foreach ($servicesFaqs as $faq) {
            Faq::create(array_merge($faq, [
                'faq_category_id' => $servicesCategory->id,
                'is_published' => true,
            ]));
        }

        // Technical Support FAQs
        $technicalFaqs = [
            [
                'question' => 'I cannot log in to my account. What should I do?',
                'answer' => '<p>If you\'re having login issues:</p>
                    <ol>
                        <li>Make sure you\'re using the correct email and password</li>
                        <li>Check if Caps Lock is on</li>
                        <li>Try resetting your password</li>
                        <li>Clear your browser cache and cookies</li>
                        <li>Try a different browser</li>
                        <li>Contact support if the problem persists</li>
                    </ol>',
                'order' => 1,
            ],
            [
                'question' => 'The website is not loading properly. How can I fix it?',
                'answer' => '<p>Try these troubleshooting steps:</p>
                    <ul>
                        <li>Refresh the page (Ctrl+F5 or Cmd+Shift+R)</li>
                        <li>Clear your browser cache</li>
                        <li>Disable browser extensions temporarily</li>
                        <li>Try a different browser (Chrome, Firefox, Safari)</li>
                        <li>Check your internet connection</li>
                        <li>Update your browser to the latest version</li>
                    </ul>
                    <p>If issues persist, contact our technical support team.</p>',
                'order' => 2,
            ],
            [
                'question' => 'How do I upload documents?',
                'answer' => '<p>To upload documents:</p>
                    <ol>
                        <li>Go to your profile or application page</li>
                        <li>Click on the "Upload" button</li>
                        <li>Select the document type</li>
                        <li>Choose the file from your device</li>
                        <li>Wait for the upload to complete</li>
                    </ol>
                    <p><strong>File requirements:</strong></p>
                    <ul>
                        <li>Format: PDF, JPG, PNG</li>
                        <li>Maximum size: 5MB per file</li>
                        <li>Clear and readable scans</li>
                    </ul>',
                'order' => 3,
            ],
        ];

        foreach ($technicalFaqs as $faq) {
            Faq::create(array_merge($faq, [
                'faq_category_id' => $technicalCategory->id,
                'is_published' => true,
            ]));
        }

        $this->command->info('✅ FAQ categories and FAQs seeded successfully!');
        $this->command->info('📊 Created 6 categories and '.Faq::count().' FAQs');
    }
}

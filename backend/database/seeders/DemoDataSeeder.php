<?php

namespace Database\Seeders;

use App\Models\KnowledgeChunk;
use App\Models\PromptVersion;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Diagnostics;
use App\Models\Evaluation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Knowledge Chunks (10 items)
        $chunks = [
            [
                'title' => 'Domestic Refund Policy',
                'content' => 'Our domestic refund policy allows customers to request a full refund within 30 days of purchase. The item must be unused, in its original packaging, and accompanied by a receipt. Refunds are processed back to the original payment method within 5-7 business days.',
                'tags' => json_encode(['refund', 'domestic', 'policy']),
            ],
            [
                'title' => 'Shipping Timelines',
                'content' => 'We ship all domestic orders within 2 business days. Standard shipping takes 3-5 business days, while expedited shipping takes 1-2 business days. Tracking information will be emailed to you as soon as the package leaves our warehouse.',
                'tags' => json_encode(['shipping', 'delivery', 'timeline']),
            ],
            [
                'title' => 'Account Password Reset',
                'content' => 'To reset your account password, click on the Forgot Password link on the login page. Enter your registered email address and we will send you a password reset link. The reset link is valid for 2 hours for security purposes.',
                'tags' => json_encode(['password', 'account', 'reset', 'security']),
            ],
            [
                'title' => 'Subscription Cancellation',
                'content' => 'You can cancel your subscription at any time from your account settings page. Go to Billing, click Cancel Subscription, and confirm your choice. Your access will remain active until the end of the current billing cycle, and no further charges will be made.',
                'tags' => json_encode(['subscription', 'cancel', 'billing']),
            ],
            [
                'title' => 'Data Privacy & GDPR Compliance',
                'content' => 'We take user data privacy extremely seriously and are fully compliant with GDPR regulations. Customers can request a copy of their personal data or request that their account be permanently deleted by contacting our privacy team. We store all data using industry-standard encryption protocols.',
                'tags' => json_encode(['privacy', 'gdpr', 'data', 'security']),
            ],
            [
                'title' => 'API Rate Limits',
                'content' => 'Our public API has a default rate limit of 60 requests per minute per IP address. If you exceed this rate limit, the API will return a 429 Too Many Requests response. Developers requiring higher limits can request enterprise API keys from their dashboard.',
                'tags' => json_encode(['api', 'rate-limit', 'developer']),
            ],
            [
                'title' => 'Billing Cycle Explanation',
                'content' => 'Our billing cycles run on a monthly basis starting from the date you signed up. Invoices are generated automatically and sent to your registered billing email address. Payments are processed automatically using your saved credit card or payment profile.',
                'tags' => json_encode(['billing', 'invoice', 'cycle']),
            ],
            [
                'title' => 'Enterprise SSO Setup',
                'content' => 'Enterprise SSO can be configured using SAML 2.0 or OIDC protocols. Organization administrators can access the SSO settings under the Security tab in their organization settings panel. Please consult our technical integration guide for step-by-step metadata setup.',
                'tags' => json_encode(['sso', 'enterprise', 'saml', 'security']),
            ],
            [
                'title' => 'Support Escalation Process',
                'content' => 'Standard customer support tickets are responded to within 24 hours. If a critical system issue is reported, the ticket is escalated automatically to our Tier 2 engineering team. Critical escalations aim for resolution or mitigation within 4 hours.',
                'tags' => json_encode(['support', 'escalation', 'help']),
            ],
            [
                'title' => 'Product Dashboard Overview',
                'content' => 'The product dashboard displays key operational metrics, including system health, live traffic flow, and configuration setups. Users can customize their layout by dragging widgets around or adding custom filters. Detailed data exports can be downloaded in CSV or PDF format.',
                'tags' => json_encode(['dashboard', 'product', 'overview']),
            ],
        ];

        foreach ($chunks as $chunk) {
            KnowledgeChunk::create([
                'title' => $chunk['title'],
                'content' => $chunk['content'],
                'tags' => json_decode($chunk['tags'], true),
            ]);
        }

        // 2. Seed Prompt Versions (2 items)
        $promptV1 = PromptVersion::create([
            'name' => 'Loosely Worded Prompt',
            'system_prompt' => "You are a helpful customer support assistant. Answer the customer's query using your general knowledge and the provided context if available.",
            'version' => 1,
            'status' => 'draft',
        ]);

        $promptV2 = PromptVersion::create([
            'name' => 'Strict Context Enforcement Prompt',
            'system_prompt' => "Answer only using the provided context. If the context does not contain the answer, say so explicitly. Cite which excerpt supports your answer.",
            'version' => 2,
            'status' => 'approved',
        ]);

        // 3. Seed 5 Healthy Conversations (using Prompt v2)
        $healthyData = [
            [
                'query' => 'How do I reset my password?',
                'response' => 'Based on our Account Password Reset policy, you can reset your password by clicking the "Forgot Password" link on the login page. Enter your registered email address to receive a password reset link, which will remain valid for 2 hours for security purposes.',
                'chunks' => [3],
                'relevance' => 1.0,
                'groundedness' => 0.9,
            ],
            [
                'query' => 'Can I cancel my subscription at any time?',
                'response' => 'Yes, you can cancel your subscription at any time via your account settings page. Navigate to the Billing section, click "Cancel Subscription", and confirm. Your access will remain active until the end of the current billing cycle, and no further charges will occur.',
                'chunks' => [4],
                'relevance' => 1.0,
                'groundedness' => 0.95,
            ],
            [
                'query' => 'What are the shipping timelines?',
                'response' => 'We ship all domestic orders within 2 business days. Standard shipping takes 3-5 business days, while expedited shipping takes 1-2 business days. We will email tracking information to you as soon as the package leaves our warehouse.',
                'chunks' => [2],
                'relevance' => 1.0,
                'groundedness' => 0.92,
            ],
            [
                'query' => 'What is the rate limit for the API?',
                'response' => 'Our public API has a default rate limit of 60 requests per minute per IP address. Exceeding this rate limit returns a 429 Too Many Requests response. Developers requiring higher limits can request enterprise keys from their dashboard.',
                'chunks' => [6],
                'relevance' => 1.0,
                'groundedness' => 0.88,
            ],
            [
                'query' => 'Is the system GDPR compliant?',
                'response' => 'Yes, we take user data privacy seriously and are fully compliant with GDPR regulations. You may request a copy of your personal data or request that your account be permanently deleted by contacting our privacy team. Data is encrypted.',
                'chunks' => [5],
                'relevance' => 1.0,
                'groundedness' => 0.95,
            ],
        ];

        foreach ($healthyData as $index => $item) {
            $conv = Conversation::create([
                'prompt_version_id' => $promptV2->id,
                'title' => 'Healthy Flow: ' . $item['query'],
            ]);

            // User message
            $userMsg = Message::create([
                'conversation_id' => $conv->id,
                'role' => 'user',
                'content' => $item['query'],
                'source_chunk_ids' => null,
            ]);

            // Assistant message
            $assistantMsg = Message::create([
                'conversation_id' => $conv->id,
                'role' => 'assistant',
                'content' => $item['response'],
                'source_chunk_ids' => $item['chunks'],
            ]);

            // Diagnostics
            Diagnostics::create([
                'message_id' => $assistantMsg->id,
                'retrieval_relevance_avg' => $item['relevance'],
                'groundedness_score' => $item['groundedness'],
                'root_cause' => 'healthy',
                'suggested_fix' => null,
                'missing_terms' => [],
                'latency_ms' => rand(180, 350),
                'safety_flag' => false,
                'provider_name' => 'mock',
            ]);

            // Evaluation
            Evaluation::create([
                'message_id' => $assistantMsg->id,
                'reviewer_name' => 'John Doe',
                'rating' => rand(4, 5),
                'flag' => 'good',
                'notes' => 'Response matches policy exactly and was helpful.',
            ]);
        }

        // 4. Seed 1 Deliberately Broken Conversation (using Prompt v1)
        $brokenConv = Conversation::create([
            'prompt_version_id' => $promptV1->id,
            'title' => 'Broken Flow: International Refund Query',
        ]);

        $userBrokenMsg = Message::create([
            'conversation_id' => $brokenConv->id,
            'role' => 'user',
            'content' => "What's the refund window for an international order?",
            'source_chunk_ids' => null,
        ]);

        $assistantBrokenMsg = Message::create([
            'conversation_id' => $brokenConv->id,
            'role' => 'assistant',
            'content' => "Our international refund policy allows you to request a full refund within 60 days of the shipping date for international orders.",
            'source_chunk_ids' => [], // empty because no chunk matched
        ]);

        // Diagnostics showing knowledge gap and hallucination
        Diagnostics::create([
            'message_id' => $assistantBrokenMsg->id,
            'retrieval_relevance_avg' => 0.05,
            'groundedness_score' => 0.1,
            'root_cause' => 'knowledge_gap', // first check match
            'suggested_fix' => 'No matching knowledge found for: international, refund. Add a knowledge article covering this topic.',
            'missing_terms' => ['international', 'refund'],
            'latency_ms' => 1240,
            'safety_flag' => false,
            'provider_name' => 'mock',
        ]);

        // Evaluation flagging hallucination
        Evaluation::create([
            'message_id' => $assistantBrokenMsg->id,
            'reviewer_name' => 'Lead Auditor',
            'rating' => 1,
            'flag' => 'hallucination',
            'notes' => 'We do not offer international refunds and do not have an international refund policy. The AI completely hallucinated a 60-day window because it used Prompt Version 1 which does not restrict it to context.',
        ]);
    }
}

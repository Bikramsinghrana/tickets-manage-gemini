# AI Chatbot Feature

A personalized AI-powered chatbot integrated into the Ticket Management System, providing contextual assistance to users.

## Overview

The AI chatbot uses **Google Gemini 2.5 Flash** (free tier) to provide intelligent, context-aware responses about the ticket management system. It dynamically injects real data from your database to personalize responses for each user.

## Features

- **Real AI Responses** - Powered by Google Gemini 2.5 Flash
- **Personalized Context** - Uses actual database data (categories, tickets, user info)
- **Role-Based Responses** - Different context for Admin, Developer, and Users
- **Modern UI** - Floating chat button with smooth animations
- **Mobile Responsive** - Works on all screen sizes
- **Rate Limited** - Protected against abuse (30 requests/min per user)
- **Free Tier** - No cost for up to 60 requests/minute

## Setup Instructions

### 1. Get a Free Gemini API Key

1. Visit [Google AI Studio](https://aistudio.google.com/app/apikey)
2. Sign in with your Google account
3. Click **"Create API Key"**
4. Copy the generated key

### 2. Configure Environment

Add the API key to your `.env` file:

```env
GEMINI_API_KEY=your_api_key_here
```

### 3. Clear Config Cache (if needed)

```bash
php artisan config:clear
php artisan cache:clear
```

### 4. Verify Installation

1. Log in to the application
2. Look for the purple robot icon in the bottom-right corner
3. Click to open the chat window
4. Ask a question like "How do I create a ticket?"

## Files Created/Modified

### New Files

| File | Description |
|------|-------------|
| `app/Http/Controllers/ChatbotController.php` | Main controller handling AI requests |
| `resources/views/components/chatbot.blade.php` | Chat UI component with styles and JavaScript |

### Modified Files

| File | Changes |
|------|---------|
| `config/services.php` | Added Gemini API configuration |
| `routes/web.php` | Added `/api/chatbot` route |
| `.env.example` | Added `GEMINI_API_KEY` variable |
| `resources/views/layouts/app.blade.php` | Included chatbot component |

## How It Works

### Architecture

```
┌──────────────┐     ┌─────────────────┐     ┌─────────────┐
│   Browser    │────▶│  Laravel API    │────▶│  Gemini AI  │
│  (Frontend)  │◀────│  (Backend)      │◀────│  (Google)   │
└──────────────┘     └─────────────────┘     └─────────────┘
                              │
                              ▼
                     ┌─────────────────┐
                     │    Database     │
                     │  (Context Data) │
                     └─────────────────┘
```

### Context Injection Flow

1. User sends a message
2. Backend fetches real data from database:
   - Active categories
   - User's role and name
   - User's ticket statistics
   - User's recent tickets (last 5)
   - System-wide statistics
3. Data is formatted into a context prompt
4. Context + user question sent to Gemini AI
5. AI generates personalized response
6. Response returned to frontend

### Data Included in Context

| Data Type | Source | Purpose |
|-----------|--------|---------|
| Categories | `categories` table | Help users understand available options |
| User Info | `users` table | Personalize greetings and responses |
| User's Tickets | `tickets` table | Answer questions about their tickets |
| Role Permissions | Spatie roles | Provide role-appropriate guidance |
| System Stats | Aggregated data | Give overview of system status |

## Customization

### Modifying the AI Behavior

Edit the context methods in `ChatbotController.php`:

```php
// Base system description
private function getBaseContext(): string

// Categories from database
private function getCategoriesContext(): string

// User-specific data
private function getUserContext($user): string

// System statistics
private function getSystemStats(): string

// Response guidelines
private function getResponseGuidelines(): string
```

### Changing AI Parameters

In `ChatbotController.php`, modify the `generationConfig`:

```php
'generationConfig' => [
    'temperature' => 0.7,      // 0-1: Higher = more creative
    'topK' => 40,              // Token selection diversity
    'topP' => 0.95,            // Nucleus sampling
    'maxOutputTokens' => 1024, // Max response length
],
```

### Styling the Chat UI

Edit the `<style>` section in `resources/views/components/chatbot.blade.php`:

```css
/* Change primary color */
.chatbot-toggle {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
}

/* Change chat window size */
.chatbot-window {
    width: 380px;
    height: 520px;
}
```

### Adding Quick Action Buttons

In `chatbot.blade.php`, add new buttons:

```html
<button class="quick-action-btn" data-message="Your question here">
    <i class="fas fa-icon"></i> Button Label
</button>
```

## API Reference

### Endpoint

```
POST /api/chatbot
```

### Request

```json
{
    "message": "How do I create a ticket?"
}
```

### Response (Success)

```json
{
    "success": true,
    "response": "To create a new ticket..."
}
```

### Response (Error)

```json
{
    "success": false,
    "response": "Error message here"
}
```

### Rate Limiting

- **Limit**: 30 requests per minute per user
- **Scope**: Authenticated users only

## Troubleshooting

### Chatbot not responding

1. Check if `GEMINI_API_KEY` is set in `.env`
2. Clear config cache: `php artisan config:clear`
3. Check browser console for JavaScript errors
4. Check Laravel logs: `storage/logs/laravel.log`

### "AI service is not configured" error

```bash
# Ensure API key is in .env
echo "GEMINI_API_KEY=your_key_here" >> .env

# Clear cache
php artisan config:clear
```

### Rate limit errors

The chatbot is limited to 30 requests/minute per user. If users hit this limit:
- Wait 1 minute before trying again
- Adjust limit in `routes/web.php`:

```php
Route::post('/api/chatbot', [ChatbotController::class, 'chat'])
    ->middleware(['auth', 'throttle:60,1']) // Change 30 to 60
```

### Gemini API errors

Check the API response in Laravel logs. Common issues:
- Invalid API key
- Quota exceeded (free tier: 60 RPM)
- Content blocked by safety filters

## Security Considerations

1. **API Key Protection**: Key is stored server-side, never exposed to browser
2. **Authentication Required**: Only logged-in users can use chatbot
3. **Rate Limiting**: Prevents abuse and cost overruns
4. **Input Validation**: Message length limited to 1000 characters
5. **Safety Filters**: Gemini's built-in content moderation enabled

## Cost Information

### Google Gemini Free Tier

- **Model**: Gemini 1.5 Flash
- **Requests**: 60 per minute
- **Daily Limit**: 1,500 requests/day
- **Cost**: $0 (free)

### Upgrading to Paid Tier

If you need more capacity, Gemini offers pay-as-you-go pricing:
- Input: $0.075 per 1M tokens
- Output: $0.30 per 1M tokens

## Future Enhancements

Potential improvements:

1. **Conversation History** - Remember previous messages in session
2. **RAG Integration** - Vector search for documentation
3. **Multi-language Support** - Detect and respond in user's language
4. **Voice Input** - Speech-to-text for accessibility
5. **Ticket Actions** - Create tickets directly from chat
6. **Analytics** - Track common questions and improve responses

## Support

For issues or questions about this feature:
1. Check the troubleshooting section above
2. Review Laravel logs for error details
3. Ensure all environment variables are set correctly

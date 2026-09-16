const express = require('express');

const app = express();
app.use(express.json());

app.get('/', (req, res) => {
    res.json({
        service: 'Ditco Notification Service',
        status: 'running',
        endpoints: [
            {
                method: 'GET',
                path: '/',
                description: 'Service info',
            },
            {
                method: 'POST',
                path: '/notify',
                description: 'Receive a notification event',
            },
        ],
    });
});

app.post('/notify', (req, res) => {
    const { event, business_name, email } = req.body;

    console.log(`[NOTIFICATION] ${event}: Name: ${business_name} Email: ${email} registered at ${new Date().toISOString()}`);

    res.status(200).json({ status: 'notified' });
});

const PORT = process.env.PORT || 4000;

app.listen(PORT, () => console.log(`Notification service running on port ${PORT}`));
 
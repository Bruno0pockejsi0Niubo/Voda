const express = require('express');
const bodyParser = require('body-parser');
const mysql = require('mysql2/promise');

const app = express();
app.use(bodyParser.json());

// Získat DB přihlašovací údaje z env:
const { DB_HOST, DB_USER, DB_PASS, DB_NAME } = process.env;
let db;

// Připojení k MySQL:
async function initDB() {
  db = await mysql.createPool({
    host: DB_HOST,
    user: DB_USER,
    password: DB_PASS,
    database: DB_NAME,
  });
}
initDB();

// Health-check endpoint
app.get('/health', (req, res) => {
  res.json({ status: 'OK', service: 'Node.js' });
});

// Endpoint pro nahrávání consumption
app.post('/api/v1/consumption/upload', async (req, res) => {
  try {
    const data = req.body; 
    // Očekáváme např. pole záznamů
    // data = [{ meter_id: 1, period_from: '2024-08-26', period_to: '2024-09-25', value: 123.45 }, ...]

    if (!Array.isArray(data)) {
      return res.status(400).json({ error: 'Data must be an array of consumption records.' });
    }

    const insertQuery = `
      INSERT INTO consumption (meter_id, period_from, period_to, value)
      VALUES (?, ?, ?, ?)
    `;

    for (const record of data) {
      await db.query(insertQuery, [
        record.meter_id,
        record.period_from,
        record.period_to,
        record.value,
      ]);
    }

    res.json({ message: 'Data uploaded successfully', count: data.length });
  } catch (error) {
    console.error(error);
    res.status(500).json({ error: 'Server error' });
  }
});

// Spuštění serveru
const PORT = 3000;
app.listen(PORT, () => {
  console.log(`Node.js server listening on port ${PORT}`);
});

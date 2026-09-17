#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <DHT.h>

const char* ssid = "MASUKAN_NAMA_WIFI";
const char* password = "MASUKAN_PASSWORD_WIFI";
const char* serverURL = "http://MASUKAN_IP_KAMU/iot-dashboard/simpan_data.php";

#define DHTPIN D4
#define DHTTYPE DHT22
#define TRIG_PIN D5
#define ECHO_PIN D6

DHT dht(DHTPIN, DHTTYPE);

float bacaJarak() {
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);

  long durasi = pulseIn(ECHO_PIN, HIGH, 30000);
  if (durasi == 0) return -1;

  return durasi * 0.0343 / 2;
}

void setup() {
  Serial.begin(115200);
  dht.begin();

  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);

  WiFi.begin(ssid, password);
  Serial.print("Menghubungkan WiFi");

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\nWiFi terhubung!");
  Serial.print("IP ESP8266: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  float suhu = dht.readTemperature();
  float kelembapan = dht.readHumidity();
  float jarak = bacaJarak();

  if (isnan(suhu) || isnan(kelembapan) || jarak < 0) {
    Serial.println("Gagal membaca sensor");
    delay(5000);
    return;
  }

  Serial.printf("Suhu: %.2f C\n", suhu);
  Serial.printf("Kelembapan: %.2f %%\n", kelembapan);
  Serial.printf("Jarak: %.2f cm\n", jarak);

  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;

    http.begin(client, serverURL);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String data = "suhu=" + String(suhu, 2) +
                  "&kelembapan=" + String(kelembapan, 2) +
                  "&jarak=" + String(jarak, 2);

    int code = http.POST(data);

    Serial.print("HTTP Code: ");
    Serial.println(code);

    if (code > 0) {
      Serial.println("Response: " + http.getString());
    } else {
      Serial.println("Gagal mengirim data");
    }

    http.end();
  }

  delay(5000);
}
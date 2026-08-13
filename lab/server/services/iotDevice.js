import { clamp, finite } from "./math.js";
import { sensorThermal, stm32Adc } from "./thermal.js";

export class VirtualBoard {
  constructor(kind = "esp32") {
    this.kind = kind === "stm32" ? "stm32" : "esp32";
    this.ambient = 24.6;
    this.temp = 24.6;
    this.humidity = 43;
    this.lux = 180;
    this.rssi = -58;
    this.uptime = 0;
    this.gpio = {
      2: 0,
      4: 0,
      5: 1,
      18: 0,
      19: 0,
    };
    this.pwm = 40;
    this.leds = { power: true, user: false, rgb: { r: 20, g: 180, b: 200 } };
    this.tools = [];
    this.lightTool = 0;
    this.serial = [];
    this.bootMs = Date.now();
    this.lastLog = 0;
    this.tickN = 0;
    this.imu = { roll: 0, pitch: 0, yaw: 0 };
    this.pushLog("boot", this.kind === "esp32"
      ? "ESP-ROM:esp32s3-20210327"
      : "STM32F401RE · HAL 1.27.1");
    this.pushLog("sys", this.kind === "esp32"
      ? "I (28) boot: SPIWP:0xee  ·  WiFi MAC 7c:9e:bd:f2:11:4a"
      : "HSE 8MHz · PLL 84MHz · ADC1 12-bit");
    this.pushLog("ok", this.kind === "esp32"
      ? "I (312) physio: lab firmware 1.4.2 ready"
      : "physio_lab_init() = HAL_OK");
  }

  pushLog(level, message) {
    const ms = Date.now() - this.bootMs;
    this.serial.push({ t: ms, level, message });
    if (this.serial.length > 80) this.serial.splice(0, this.serial.length - 80);
  }

  applyClient(msg) {
    if (!msg || typeof msg !== "object") return;
    if (msg.type === "tools" && Array.isArray(msg.tools)) {
      this.tools = msg.tools.slice(0, 4).map((t) => ({
        kind: String(t.kind || "flame").slice(0, 16),
        distance: clamp(finite(t.distance, 1), 0, 2),
        intensity: clamp(finite(t.intensity, 0), 0, 1),
      }));
    }
    if (msg.type === "light") {
      this.lightTool = clamp(finite(msg.intensity, 0), 0, 1);
    }
    if (msg.type === "gpio") {
      const pin = String(msg.pin);
      if (Object.prototype.hasOwnProperty.call(this.gpio, pin)) {
        this.gpio[pin] = msg.value ? 1 : 0;
        this.pushLog("gpio", `GPIO${pin} → ${this.gpio[pin] ? "HIGH" : "LOW"}`);
      }
    }
    if (msg.type === "pwm") {
      this.pwm = clamp(Math.round(finite(msg.value, 0)), 0, 255);
      this.pushLog("pwm", `LEDC ch0 duty=${this.pwm} / 255`);
    }
    if (msg.type === "rgb") {
      this.leds.rgb = {
        r: clamp(Math.round(finite(msg.r, 0)), 0, 255),
        g: clamp(Math.round(finite(msg.g, 0)), 0, 255),
        b: clamp(Math.round(finite(msg.b, 0)), 0, 255),
      };
    }
    if (msg.type === "imu") {
      this.imu = {
        roll: clamp(finite(msg.roll, 0), -45, 45),
        pitch: clamp(finite(msg.pitch, 0), -45, 45),
        yaw: clamp(finite(msg.yaw, 0), -45, 45),
      };
    }
    if (msg.type === "reset") {
      this.temp = this.ambient;
      this.humidity = 43;
      this.pushLog("sys", "soft reset · sensors re-zeroed");
    }
  }

  tick(dt = 0.08) {
    this.tickN += 1;
    this.uptime += dt;
    const th = sensorThermal({
      ambient: this.ambient,
      temp: this.temp,
      humidity: this.humidity,
      dt,
      tau: this.kind === "stm32" ? 0.9 : 1.35,
      tools: this.tools,
    });
    this.temp = th.temp;
    this.humidity = th.humidity;

    const baseLux = 120 + 40 * Math.sin(this.uptime * 0.15);
    this.lux = clamp(baseLux + this.lightTool * 780 + (Math.random() - 0.5) * 6, 0, 1200);
    this.rssi = clamp(-52 - Math.random() * 14 - (this.temp > 50 ? 4 : 0), -90, -30);
    this.leds.user = this.gpio[2] === 1 || this.pwm > 20;

    const adc = stm32Adc(this.temp, this.kind === "stm32" ? 3.3 : 3.3, this.kind === "stm32" ? 12 : 12);

    if (Date.now() - this.lastLog > 900) {
      this.lastLog = Date.now();
      if (this.kind === "esp32") {
        this.pushLog("i", `[DHT22] t=${this.temp.toFixed(2)}C  rh=${this.humidity.toFixed(1)}%  lux=${this.lux.toFixed(0)}`);
        if (th.coupling > 0.35) {
          this.pushLog("w", `[THERMAL] coupling=${th.coupling.toFixed(2)}  Teq=${th.equilibrium.toFixed(1)}C`);
        }
      } else {
        this.pushLog("i", `ADC1_IN0=${adc.code}  (${adc.volts.toFixed(3)} V)  T=${this.temp.toFixed(2)} °C`);
      }
    }

    return {
      kind: this.kind,
      temp: this.temp,
      humidity: this.humidity,
      lux: this.lux,
      rssi: this.rssi,
      adc,
      gpio: this.gpio,
      pwm: this.pwm,
      leds: this.leds,
      imu: this.imu,
      uptime: this.uptime,
      coupling: th.coupling,
      serial: this.serial.slice(-12),
    };
  }
}

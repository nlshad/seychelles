<?php
/**
 * Seychelles International Cargo LLC - Admin CBM & Volumetric Calculator
 */
require_once __DIR__ . '/includes/admin_header.php';
?>

<div style="margin-bottom: 1.5rem;">
  <h3 style="font-family:'Outfit', sans-serif; font-weight:700; font-size:1.5rem;">
    <i class="fa-solid fa-calculator text-primary me-2"></i>CBM & Volumetric Freight Calculator
  </h3>
  <p style="color:var(--admin-muted); font-size:0.9rem;">
    Calculate total cubic meters (CBM), air freight volumetric weight, sea freight CBM, container capacity utilization, and quick cost estimates for customer quotes.
  </p>
</div>

<div class="panel-card" style="padding: 2rem;">
  <form id="cbmForm" onsubmit="return false;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
      <h4 style="font-family:'Outfit', sans-serif; font-weight:700;">Package Line Items</h4>
      <button type="button" class="btn-sm btn-admin-primary" onclick="addRow()">
        <i class="fa-solid fa-plus me-1"></i> Add Another Package
      </button>
    </div>

    <!-- Multi-Item Cargo Table -->
    <div class="table-responsive" style="margin-bottom: 2rem;">
      <table class="admin-table" id="cbmTable">
        <thead>
          <tr>
            <th style="width:10%;">Qty</th>
            <th>Length (cm)</th>
            <th>Width (cm)</th>
            <th>Height (cm)</th>
            <th>Gross Wt (kg/pc)</th>
            <th>Item CBM (m³)</th>
            <th>Vol. Wt (Air kg)</th>
            <th style="text-align:right;">Action</th>
          </tr>
        </thead>
        <tbody id="cbmRows">
          <tr>
            <td>
              <input type="number" class="calc-input qty" value="1" min="1" oninput="calculateCBM()" style="width:100%; padding:0.5rem; border:1px solid var(--admin-border); border-radius:6px;">
            </td>
            <td>
              <input type="number" step="0.1" class="calc-input length" value="50" min="0" oninput="calculateCBM()" style="width:100%; padding:0.5rem; border:1px solid var(--admin-border); border-radius:6px;">
            </td>
            <td>
              <input type="number" step="0.1" class="calc-input width" value="40" min="0" oninput="calculateCBM()" style="width:100%; padding:0.5rem; border:1px solid var(--admin-border); border-radius:6px;">
            </td>
            <td>
              <input type="number" step="0.1" class="calc-input height" value="40" min="0" oninput="calculateCBM()" style="width:100%; padding:0.5rem; border:1px solid var(--admin-border); border-radius:6px;">
            </td>
            <td>
              <input type="number" step="0.1" class="calc-input weight" value="15" min="0" oninput="calculateCBM()" style="width:100%; padding:0.5rem; border:1px solid var(--admin-border); border-radius:6px;">
            </td>
            <td class="res-cbm" style="font-weight:700; color:var(--color-primary);">0.080 m³</td>
            <td class="res-air" style="font-weight:700; color:var(--admin-text);">13.33 kg</td>
            <td style="text-align:right;">
              <button type="button" class="btn-sm btn-admin-danger" onclick="removeRow(this)" title="Remove Item"><i class="fa-solid fa-trash"></i></button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Rate Estimator Input Row -->
    <div style="background:#F8FAFC; border:1px solid var(--admin-border); border-radius:var(--radius-md); padding:1.5rem; margin-bottom:2rem;">
      <h4 style="font-family:'Outfit', sans-serif; font-size:1.05rem; margin-bottom:1rem;">Optional Rate & Documentation Fee Calculation</h4>
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1.25rem;">
        <div>
          <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Sea Freight Rate / CBM (USD)</label>
          <input type="number" id="seaRate" value="120" step="1" oninput="calculateCBM()" style="width:100%; padding:0.6rem; border:1px solid var(--admin-border); border-radius:6px;">
        </div>
        <div>
          <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Documentation Charge (USD)</label>
          <input type="number" id="docCharge" value="100" step="1" oninput="calculateCBM()" style="width:100%; padding:0.6rem; border:1px solid var(--admin-border); border-radius:6px;">
        </div>
        <div>
          <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Air Cargo Rate / KG (USD)</label>
          <input type="number" id="airRate" value="4.5" step="0.1" oninput="calculateCBM()" style="width:100%; padding:0.6rem; border:1px solid var(--admin-border); border-radius:6px;">
        </div>
      </div>
    </div>
  </form>
</div>

<!-- Results Summary Grid -->
<div class="stats-grid" style="margin-top:2rem; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));">
  <!-- Total CBM -->
  <div class="stat-card" style="border-top:4px solid var(--color-primary);">
    <div>
      <div class="stat-label">Total Volume (CBM)</div>
      <div class="stat-val" id="totalCBMVal">0.080 m³</div>
      <small style="color:var(--admin-muted); display:block; margin-top:0.25rem;" id="totalCuFt">2.83 cu ft</small>
    </div>
    <div class="stat-icon"><i class="fa-solid fa-cube"></i></div>
  </div>

  <!-- Gross Weight vs Volumetric Weight -->
  <div class="stat-card" style="border-top:4px solid #10B981;">
    <div>
      <div class="stat-label">Actual Gross Weight</div>
      <div class="stat-val" id="totalGrossVal">15.00 kg</div>
      <small style="color:var(--admin-muted); display:block; margin-top:0.25rem;" id="totalLbs">33.07 lbs</small>
    </div>
    <div class="stat-icon" style="background:#ECFDF5; color:#10B981;"><i class="fa-solid fa-weight-hanging"></i></div>
  </div>

  <!-- Air Freight Chargeable Weight -->
  <div class="stat-card" style="border-top:4px solid #F59E0B;">
    <div>
      <div class="stat-label">Air Chargeable Weight</div>
      <div class="stat-val" id="airChargeableVal">15.00 kg</div>
      <small style="color:var(--admin-muted); display:block; margin-top:0.25rem;" id="airChargeNote">Actual Wt applies</small>
    </div>
    <div class="stat-icon" style="background:#FFF7ED; color:#F59E0B;"><i class="fa-solid fa-plane-up"></i></div>
  </div>

  <!-- Estimated Sea Cost -->
  <div class="stat-card" style="border-top:4px solid #8B5CF6;">
    <div>
      <div class="stat-label">Estimated Sea Freight Cost</div>
      <div class="stat-val" id="estSeaCost">$220.00</div>
      <small style="color:var(--admin-muted); display:block; margin-top:0.25rem;" id="seaCostBreakdown">Freight: $120.00 + Doc: $100.00</small>
    </div>
    <div class="stat-icon" style="background:#F5F3FF; color:#8B5CF6;"><i class="fa-solid fa-ship"></i></div>
  </div>

  <!-- Estimated Air Cargo Cost -->
  <div class="stat-card" style="border-top:4px solid #00E5FF;">
    <div>
      <div class="stat-label">Estimated Air Cargo Cost</div>
      <div class="stat-val" id="estAirCost">$67.50</div>
      <small style="color:var(--admin-muted); display:block; margin-top:0.25rem;" id="airCostBreakdown">15.00 kg × $4.50/kg (Freight Only)</small>
      <small style="color:#D97706; display:block; margin-top:0.35rem; font-weight:600; font-size:0.75rem;">
        <i class="fa-solid fa-circle-info me-1"></i>Excludes variable airline documentation & AWB fees
      </small>
    </div>
    <div class="stat-icon" style="background:#E0F7FA; color:#00B8D4;"><i class="fa-solid fa-plane-departure"></i></div>
  </div>
</div>

<!-- Container Capacity Utilization Indicator -->
<div class="panel-card" style="padding:1.5rem; margin-top:1.5rem;">
  <h4 style="font-family:'Outfit', sans-serif; font-size:1.05rem; margin-bottom:1rem;">
    <i class="fa-solid fa-ship me-2 text-primary"></i>Container Space Utilization
  </h4>
  <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1.5rem;">
    <div>
      <div style="display:flex; justify-content:space-between; font-size:0.85rem; font-weight:600; margin-bottom:0.35rem;">
        <span>20ft Standard Container (33 CBM)</span>
        <span id="c20Pct">0.2%</span>
      </div>
      <div style="background:#E2E8F0; height:8px; border-radius:4px; overflow:hidden;">
        <div id="c20Bar" style="background:var(--color-primary); width:0.2%; height:100%; transition:width 0.3s;"></div>
      </div>
    </div>
    <div>
      <div style="display:flex; justify-content:space-between; font-size:0.85rem; font-weight:600; margin-bottom:0.35rem;">
        <span>40ft High Cube Container (76 CBM)</span>
        <span id="c40Pct">0.1%</span>
      </div>
      <div style="background:#E2E8F0; height:8px; border-radius:4px; overflow:hidden;">
        <div id="c40Bar" style="background:#10B981; width:0.1%; height:100%; transition:width 0.3s;"></div>
      </div>
    </div>
  </div>

  <div style="margin-top:1.5rem; text-align:right;">
    <button class="btn-sm btn-admin-secondary" onclick="copySummaryText()">
      <i class="fa-solid fa-copy me-1"></i> Copy Calculation Summary for WhatsApp/Quote
    </button>
  </div>
</div>

<script>
function addRow() {
  const tbody = document.getElementById('cbmRows');
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>
      <input type="number" class="calc-input qty" value="1" min="1" oninput="calculateCBM()" style="width:100%; padding:0.5rem; border:1px solid var(--admin-border); border-radius:6px;">
    </td>
    <td>
      <input type="number" step="0.1" class="calc-input length" value="50" min="0" oninput="calculateCBM()" style="width:100%; padding:0.5rem; border:1px solid var(--admin-border); border-radius:6px;">
    </td>
    <td>
      <input type="number" step="0.1" class="calc-input width" value="40" min="0" oninput="calculateCBM()" style="width:100%; padding:0.5rem; border:1px solid var(--admin-border); border-radius:6px;">
    </td>
    <td>
      <input type="number" step="0.1" class="calc-input height" value="40" min="0" oninput="calculateCBM()" style="width:100%; padding:0.5rem; border:1px solid var(--admin-border); border-radius:6px;">
    </td>
    <td>
      <input type="number" step="0.1" class="calc-input weight" value="15" min="0" oninput="calculateCBM()" style="width:100%; padding:0.5rem; border:1px solid var(--admin-border); border-radius:6px;">
    </td>
    <td class="res-cbm" style="font-weight:700; color:var(--color-primary);">0.080 m³</td>
    <td class="res-air" style="font-weight:700; color:var(--admin-text);">13.33 kg</td>
    <td style="text-align:right;">
      <button type="button" class="btn-sm btn-admin-danger" onclick="removeRow(this)" title="Remove Item"><i class="fa-solid fa-trash"></i></button>
    </td>
  `;
  tbody.appendChild(tr);
  calculateCBM();
}

function removeRow(btn) {
  const rows = document.querySelectorAll('#cbmRows tr');
  if (rows.length > 1) {
    btn.closest('tr').remove();
    calculateCBM();
  }
}

function calculateCBM() {
  let grandTotalCBM = 0;
  let grandTotalGrossWeight = 0;
  let grandTotalAirVolWeight = 0;

  const rows = document.querySelectorAll('#cbmRows tr');
  rows.forEach(row => {
    const qty = parseFloat(row.querySelector('.qty').value) || 0;
    const l = parseFloat(row.querySelector('.length').value) || 0;
    const w = parseFloat(row.querySelector('.width').value) || 0;
    const h = parseFloat(row.querySelector('.height').value) || 0;
    const wt = parseFloat(row.querySelector('.weight').value) || 0;

    // Single item volume in cubic meters: (L x W x H) / 1,000,000
    const singleCBM = (l * w * h) / 1000000;
    const itemTotalCBM = singleCBM * qty;

    // Single item air volumetric weight (divisor 6000 standard IATA): (L x W x H) / 6000
    const singleAirVolWeight = (l * w * h) / 6000;
    const itemTotalAirVolWeight = singleAirVolWeight * qty;

    const itemTotalGrossWeight = wt * qty;

    row.querySelector('.res-cbm').innerText = itemTotalCBM.toFixed(3) + ' m³';
    row.querySelector('.res-air').innerText = itemTotalAirVolWeight.toFixed(2) + ' kg';

    grandTotalCBM += itemTotalCBM;
    grandTotalGrossWeight += itemTotalGrossWeight;
    grandTotalAirVolWeight += itemTotalAirVolWeight;
  });

  // Display Total CBM & CuFt
  document.getElementById('totalCBMVal').innerText = grandTotalCBM.toFixed(3) + ' m³';
  const totalCuFt = grandTotalCBM * 35.3147;
  document.getElementById('totalCuFt').innerText = totalCuFt.toFixed(2) + ' cu ft';

  // Display Gross Weight & Lbs
  document.getElementById('totalGrossVal').innerText = grandTotalGrossWeight.toFixed(2) + ' kg';
  const totalLbs = grandTotalGrossWeight * 2.20462;
  document.getElementById('totalLbs').innerText = totalLbs.toFixed(2) + ' lbs';

  // Air Chargeable Weight (Max of Gross Weight vs Volumetric Weight)
  const airChargeable = Math.max(grandTotalGrossWeight, grandTotalAirVolWeight);
  document.getElementById('airChargeableVal').innerText = airChargeable.toFixed(2) + ' kg';
  if (grandTotalAirVolWeight > grandTotalGrossWeight) {
    document.getElementById('airChargeNote').innerText = 'Volumetric Wt applies (' + grandTotalAirVolWeight.toFixed(2) + ' kg)';
  } else {
    document.getElementById('airChargeNote').innerText = 'Actual Gross Wt applies';
  }

  // Cost Estimations
  const seaRate = parseFloat(document.getElementById('seaRate').value) || 0;
  const docCharge = parseFloat(document.getElementById('docCharge').value) || 0;
  const airRate = parseFloat(document.getElementById('airRate').value) || 0;

  // Minimum 1 CBM for LCL ocean freight pricing standard
  const billableSeaCBM = Math.max(grandTotalCBM, 1);
  const seaFreightBaseCost = billableSeaCBM * seaRate;
  const totalSeaCost = seaFreightBaseCost + docCharge;

  // Air Cargo Cost = Chargeable Weight x Air Rate per KG
  const totalAirCost = airChargeable * airRate;

  document.getElementById('estSeaCost').innerText = '$' + totalSeaCost.toFixed(2);
  document.getElementById('seaCostBreakdown').innerText = `Freight: $${seaFreightBaseCost.toFixed(2)} + Doc: $${docCharge.toFixed(2)}`;

  document.getElementById('estAirCost').innerText = '$' + totalAirCost.toFixed(2);
  document.getElementById('airCostBreakdown').innerText = `${airChargeable.toFixed(2)} kg × $${airRate.toFixed(2)}/kg (Freight Only)`;

  // Container Progress Indicators
  const c20Pct = Math.min((grandTotalCBM / 33) * 100, 100).toFixed(1);
  const c40Pct = Math.min((grandTotalCBM / 76) * 100, 100).toFixed(1);

  document.getElementById('c20Pct').innerText = c20Pct + '%';
  document.getElementById('c20Bar').style.width = c20Pct + '%';
  
  document.getElementById('c40Pct').innerText = c40Pct + '%';
  document.getElementById('c40Bar').style.width = c40Pct + '%';
}

function copySummaryText() {
  const cbm = document.getElementById('totalCBMVal').innerText;
  const gross = document.getElementById('totalGrossVal').innerText;
  const airCharge = document.getElementById('airChargeableVal').innerText;
  const totalSeaCost = document.getElementById('estSeaCost').innerText;
  const totalAirCost = document.getElementById('estAirCost').innerText;
  const docCharge = parseFloat(document.getElementById('docCharge').value) || 0;
  const seaRate = parseFloat(document.getElementById('seaRate').value) || 0;
  const airRate = parseFloat(document.getElementById('airRate').value) || 0;

  const text = `📦 *Seychelles Cargo Calculation Summary*:\n` +
               `• Total Volume: ${cbm}\n` +
               `• Gross Weight: ${gross}\n` +
               `• Air Chargeable Wt: ${airCharge}\n` +
               `-----------------------------------\n` +
               `• Sea Freight Rate: $${seaRate}/CBM\n` +
               `• Documentation Charge: $${docCharge.toFixed(2)}\n` +
               `• Total Est. Sea Freight Cost: ${totalSeaCost}\n` +
               `-----------------------------------\n` +
               `• Air Cargo Rate: $${airRate.toFixed(2)}/KG (Freight Only)\n` +
               `• Total Est. Air Cargo Cost: ${totalAirCost}\n` +
               `  *(Note: Air cargo documentation & AWB fees excluded, quoted upon booking)*`;

  navigator.clipboard.writeText(text).then(() => {
    alert('Calculation summary copied to clipboard! You can paste it into WhatsApp or customer email.');
  });
}

// Initial calculation on page load
calculateCBM();
</script>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
?>

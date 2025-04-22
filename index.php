<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Drug Info Search</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@10.2.7/dist/autoComplete.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@10.2.7/dist/css/autoComplete.min.css">
  <style>
    .autoComplete_wrapper > input {
      width: 100%;
      padding: 14px 20px;
      border-radius: 12px;
      border: 1px solid #cbd5e1;
      font-size: 16px;
      box-shadow: 0 1px 2px rgba(0,0,0,0.04);
      transition: border-color 0.2s ease;
    }
    .autoComplete_wrapper > input:focus {
      outline: none;
      border-color: #6366f1;
      box-shadow: 0 0 0 2px rgba(99,102,241,0.2);
    }

    @media print {
      body * {
        visibility: hidden;
      }
      #drug-details, #drug-details * {
        visibility: visible;
      }
      #drug-details {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        padding: 40px;
        font-family: Georgia, serif;
      }
      #drug-details h3 {
        margin-top: 20px;
        font-size: 18px;
        color: #111;
      }
      #drug-details .prose {
        font-size: 14px;
        line-height: 1.6;
        color: #333;
      }
      #drug-details .print-hidden {
        display: none !important;
      }
      #print-footer {
        display: block;
        text-align: center;
        font-size: 13px;
        margin-top: 40px;
        color: #444;
        border-top: 1px solid #ccc;
        padding-top: 10px;
      }
      a {
        color: #1a0dab;
        text-decoration: underline;
      }
    }
    .print-footer {
  display: flex;
  justify-content: center;
  align-items: center;
  text-align: center;
  font-size: 14px;
  margin-top: 40px;
  color: #444;
  border-top: 1px solid #ccc;
  padding-top: 15px;
  line-height: 1.6;
}

.print-footer a {
  color: #1a0dab;
  text-decoration: none;
}

.print-footer a:hover {
  text-decoration: underline;
}

.print-footer strong {
  color: #000;
}

.print-footer .pray-text {
  font-size: 13px;
  font-style: italic;
  color: #666;
}

  </style>
</head>
<body class="bg-gray-50 font-sans min-h-screen p-6">
  <div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-6 text-center">🔎 Search for a Drug</h1>
    <div class="autoComplete_wrapper mb-6">
      <input id="autoComplete" type="text" placeholder="Type medicine name..." />
    </div>

    <div id="drug-details" class="hidden bg-white shadow rounded-lg p-6 transition-all duration-300">
      <div class="flex items-center justify-between mb-4 print-hidden">
        <h2 id="drug-title" class="text-2xl font-semibold text-indigo-700"></h2>
        <div class="flex gap-2">
          <button onclick="window.print()" class="bg-gray-200 hover:bg-gray-300 text-sm px-3 py-1 rounded">🖨️ Print</button>
          <button id="copy-link" class="bg-blue-100 hover:bg-blue-200 text-sm px-3 py-1 rounded">🔗 Copy Link</button>
        </div>
      </div>
      <h2 id="drug-title-print" class="text-2xl font-semibold text-indigo-700 mb-4 hidden"></h2>
      <div class="grid md:grid-cols-2 gap-4 text-gray-700 text-sm">
        <div>
          <h3 class="text-md font-bold text-gray-800 mb-1">Indications:</h3>
          <div id="drug-ind" class="prose max-w-none"></div>
        </div>
        <div>
          <h3 class="text-md font-bold text-gray-800 mb-1">Adult Dosage:</h3>
          <div id="drug-adult" class="prose max-w-none"></div>
        </div>
        <div>
          <h3 class="text-md font-bold text-gray-800 mb-1">Pediatric Dosage:</h3>
          <div id="drug-ped" class="prose max-w-none"></div>
        </div>
        <div>
          <h3 class="text-md font-bold text-gray-800 mb-1">Side Effects:</h3>
          <div id="drug-side" class="prose max-w-none"></div>
        </div>
        <div>
          <h3 class="text-md font-bold text-gray-800 mb-1">Class:</h3>
          <div id="drug-clas"></div>
        </div>
        <div>
          <h3 class="text-md font-bold text-gray-800 mb-1">Mode of Action:</h3>
          <div id="drug-mode" class="prose max-w-none"></div>
        </div>
      </div>
      <div id="print-footer" class="print-footer">
  <p>
    Visit: 
    <a href="https://aywa.sd/drugs" target="_blank">aywa.sd/drugs</a> — 
    تم التطوير بواسطة <strong>عبد الرحمن مولود</strong> <br/>
    <span class="pray-text"> اطلب منكم الدعاء بظهر الغيب</span>
  </p>
</div>

    </div>
  </div>

  <script>
    const DATA_URL = "Drug.json";
    let drugs = [];

    $(document).ready(function () {
      $.getJSON(DATA_URL)
        .done(function (data) {
          drugs = data.filter(d => d.title && d.title !== "no data");

          const urlParams = new URLSearchParams(window.location.search);
          const queryDrug = urlParams.get('q');
          if (queryDrug) {
            const matchedDrug = drugs.find(d => d.title.toLowerCase() === queryDrug.toLowerCase());
            if (matchedDrug) {
              $('#autoComplete').val(matchedDrug.title);
              displayDrugDetails(matchedDrug);
            }
          }

          new autoComplete({
            selector: "#autoComplete",
            placeHolder: "Type medicine name...",
            data: {
              src: drugs.map(d => d.title),
              cache: true
            },
            resultItem: {
              highlight: true
            },
            events: {
              input: {
                selection: (event) => {
                  const value = event.detail.selection.value;
                  event.target.value = value;
                  const selectedDrug = drugs.find(d => d.title === value);
                  if (selectedDrug) displayDrugDetails(selectedDrug);
                }
              }
            }
          });
        })
        .fail(() => alert("⚠️ Failed to load Drug data. Check URL and CORS headers."));

      function displayDrugDetails(drug) {
        const queryParam = encodeURIComponent(drug.title);
        const shareLink = `${window.location.origin}${window.location.pathname}?q=${queryParam}`;

        $('#drug-title').html(cleanString(drug.title) || '');
        $('#drug-title-print').html(cleanString(drug.title) || '');
        $('#drug-ind').html(cleanString(drug.ind) || '');
        $('#drug-adult').html(cleanString(drug.adult) || '');
        $('#drug-ped').html(cleanString(drug.ped) || '');
        $('#drug-side').html(cleanString(drug.side) || '');
        $('#drug-clas').html(cleanString(drug.clas) || '');
        $('#drug-mode').html(cleanString(drug.mode) || '');
        $('#copy-link').data('link', shareLink);
        $('#drug-details').removeClass('hidden').hide().fadeIn(300);
      }
      
      function cleanString(str) {
          return str?.replace(/[^\x20-\x7E\u0600-\u06FF]/g, ' '); // يحذف الرموز غير اللاتينية والعربية
        }

      $('#copy-link').on('click', function () {
        const link = $(this).data('link');
        navigator.clipboard.writeText(link).then(() => {
          $(this).text('✅ Copied!');
          setTimeout(() => $(this).text('🔗 Copy Link'), 2000);
        });
      });
    });
  </script>
</body>
</html>

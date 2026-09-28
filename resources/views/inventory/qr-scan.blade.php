<style>
    .qr-page { background: radial-gradient(circle at 100% 0, rgba(47,154,145,.12), transparent 28rem), linear-gradient(135deg, #f4f8f8 0%, #e8f1f2 100%); }
    .qr-page-title { position: relative; display: inline-block; color: #123b5d !important; }
    .qr-page-title::after { content: ""; display: block; width: 3.5rem; height: 4px; margin-top: .45rem; border-radius: 4px; background: #d99b2b; }
    .qr-shell { border: 1px solid #d8e5e8; border-top: 4px solid #2f9a91; box-shadow: 0 16px 40px rgba(18,59,93,.08); }
    .qr-panel { border-color: #d8e5e8 !important; background: rgba(255,255,255,.82); }
    .qr-camera-panel { background: #edf6f5 !important; }
    .qr-page input:focus { border-color: #287f92; box-shadow: 0 0 0 3px rgba(47,154,145,.16); outline: none; }
    .qr-page #lookupBtn { background: #123b5d; transition: background .2s; }
    .qr-page #lookupBtn:hover { background: #1d687d; }
    .qr-page #startCamera { background: #287f92; transition: background .2s; }
    .qr-page #startCamera:hover { background: #1d687d; }
    .qr-page #stopCamera { color: #123b5d; border-color: #b9d0d5; background: #fff; }
    .qr-page #qr-reader { border-color: #b9d0d5; }
</style>

<x-app-layout>
    <x-slot name="header">
        <h2 class="qr-page-title font-semibold text-xl text-gray-800 leading-tight">Scan QR Code</h2>
    </x-slot>

    <div class="qr-page py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="qr-shell bg-white shadow sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Quick Inventory Lookup</h3>
                        <p class="text-sm text-gray-600">Scan or enter the QR identifier to retrieve item information.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="qr-panel border border-gray-200 rounded-lg p-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Scan or Enter Item Identifier</label>
                        <input id="qrInput" type="text" placeholder="Example: 1 or ITEM-000001" class="w-full border rounded-md px-3 py-2 mb-3">
                        <button id="lookupBtn" class="w-full px-4 py-2 bg-gray-900 text-white rounded-md">Lookup Item</button>
                        <p class="mt-3 text-xs text-gray-500">If the QR code identifies an item in the database, the system will display the item information and transaction history.</p>
                    </div>

                    <div class="qr-panel qr-camera-panel border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <h4 class="font-semibold mb-3">Camera / QR scanning</h4>
                        <div id="qr-reader" class="w-full rounded-md border bg-black"></div>
                        <div class="mt-3 flex gap-2">
                            <button id="startCamera" type="button" class="px-3 py-2 bg-blue-600 text-white rounded-md text-sm">Start Camera</button>
                            <button id="stopCamera" type="button" class="px-3 py-2 border border-gray-300 rounded-md text-sm">Stop</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const qrInput = document.getElementById('qrInput');
        const lookupBtn = document.getElementById('lookupBtn');
        const startCamera = document.getElementById('startCamera');
        const stopCamera = document.getElementById('stopCamera');

        function openItemRecord(value) {
            const normalized = value.trim();
            if (!normalized) {
                alert('Please enter an item identifier.');
                return;
            }

            const borrowMatch = normalized.match(/\/inventory\/qr\/borrow\/(\d+)/i);
            if (borrowMatch) {
                window.location.href = '/inventory/qr/borrow/' + borrowMatch[1];
                return;
            }

            const itemMatch = normalized.match(/\/inventory\/qr\/item\/(\d+)/i);
            const itemId = itemMatch ? itemMatch[1] : normalized.toString().replace(/[^0-9]/g, '');
            if (itemId) {
                window.location.href = '/inventory/qr/item/' + itemId;
                return;
            }

            alert('Item not found.');
        }

        lookupBtn.addEventListener('click', function () {
            openItemRecord(qrInput.value);
        });

        qrInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                openItemRecord(qrInput.value);
            }
        });

        startCamera.addEventListener('click', async function () {
            try {
                if (!window.Html5Qrcode) throw new Error('QR decoder unavailable');
                const scanner = new Html5Qrcode('qr-reader');
                window.inventoryQrScanner = scanner;
                await scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 220, height: 220 } },
                    decodedText => openItemRecord(decodedText),
                    () => {}
                );
            } catch (error) {
                alert('Camera access is unavailable on this device or browser. Please enter the item identifier manually.');
            }
        });

        stopCamera.addEventListener('click', function () {
            if (window.inventoryQrScanner) window.inventoryQrScanner.stop().catch(() => {});
        });
    </script>
</x-app-layout>

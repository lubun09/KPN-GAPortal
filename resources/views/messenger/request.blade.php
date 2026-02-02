@extends('layouts.app-sidebar')
@section('content')
<div class="flex">
    <main class="flex-1">
        <div class="bg-white shadow rounded w-full" 
             style="margin: 0.1cm 0.1cm 0 0.1cm; padding: 0.5cm;">

            <h2 class="text-2xl font-semibold mb-4 text-left">Request Messenger</h2>
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('messenger.store') }}" method="POST" enctype="multipart/form-data" id="messengerForm">
                @csrf

                <div class="space-y-6">
                    <!-- Baris 1: Jenis Barang, Alamat Asal, Alamat Tujuan -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        <!-- Jenis Barang -->
                        <div>
                            <label class="block text-sm font-medium mb-1">Jenis Barang <span class="text-red-500">*</span></label>
                            <select name="jenis_barang" id="jenis_barang" 
                                    class="w-full border border-gray-300 p-2 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    required>
                                <option value="">Pilih Jenis Barang</option>
                                <option value="paket" {{ old('jenis_barang') == 'paket' ? 'selected' : '' }}>Paket</option>
                                <option value="dokumen" {{ old('jenis_barang') == 'dokumen' ? 'selected' : '' }}>Dokumen</option>
                            </select>
                            @error('jenis_barang')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Alamat Asal -->
                        <div>
                            <label class="block text-sm font-medium mb-1">Alamat Asal <span class="text-red-500">*</span></label>
                            <input type="text" name="alamat_asal" id="alamat_asal" 
                                   class="w-full border border-gray-300 p-2 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   value="{{ old('alamat_asal', 'Gama Tower, DKI Jakarta, Indonesia') }}" 
                                   required>
                            @error('alamat_asal')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Alamat pengambilan barang</p>
                        </div>

                        <!-- Alamat Tujuan -->
                        <div>
                            <label class="block text-sm font-medium mb-1">Alamat Tujuan <span class="text-red-500">*</span></label>
                            <input type="text" name="alamat_tujuan" id="alamat_tujuan" 
                                   class="w-full border border-gray-300 p-2 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   value="{{ old('alamat_tujuan') }}" 
                                   placeholder="Masukkan alamat tujuan lengkap" 
                                   required>
                            @error('alamat_tujuan')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Alamat pengiriman barang</p>
                        </div>
                    </div>

                    <!-- Baris 2: Penerima dan No HP -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Penerima -->
                        <div>
                            <label class="block text-sm font-medium mb-1">Nama Penerima <span class="text-red-500">*</span></label>
                            <input type="text" name="penerima" id="penerima" 
                                   class="w-full border border-gray-300 p-2 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   value="{{ old('penerima') }}" 
                                   placeholder="Nama lengkap penerima" 
                                   required>
                            @error('penerima')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- No HP Penerima -->
                        <div>
                            <label class="block text-sm font-medium mb-1">No. HP Penerima <span class="text-red-500">*</span></label>
                            <input type="tel" name="no_hp_penerima" id="no_hp_penerima" 
                                   class="w-full border border-gray-300 p-2 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   value="{{ old('no_hp_penerima') }}" 
                                   placeholder="081234567890" 
                                   pattern="[0-9]{10,13}" 
                                   required>
                            @error('no_hp_penerima')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Format: 10-13 digit angka</p>
                        </div>
                    </div>

                    <!-- Deskripsi Barang -->
                    <div>
                        <label class="block text-sm font-medium mb-1">Deskripsi Barang <span class="text-red-500">*</span></label>
                        <textarea name="deskripsi" id="deskripsi" 
                                  class="w-full border border-gray-300 p-2 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                  rows="4" 
                                  placeholder="Deskripsi lengkap barang yang akan dikirim (jenis barang, ukuran, jumlah, dll.)" 
                                  required>{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Foto/Dokumen Barang -->
                    <div>
                        <label class="block text-sm font-medium mb-1">Foto/Dokumen Barang <span class="text-red-500">*</span></label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-500 transition-colors">
                            <div class="space-y-2">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400"></i>
                                <p class="text-sm text-gray-600">
                                    <span class="font-semibold">Klik untuk upload</span> atau drag & drop file di sini
                                </p>
                                <p class="text-xs text-gray-500">
                                    Format: JPG, PNG, PDF, DOC, DOCX (Maksimal: 20MB)
                                </p>
                            </div>
                            <input type="file" name="foto_barang" id="foto_barang" 
                                   class="hidden" 
                                   accept="image/*,.pdf,.doc,.docx" 
                                   required>
                        </div>
                        @error('foto_barang')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        
                        <!-- Preview Container -->
                        <div id="previewContainer" class="mt-4 hidden">
                            <p class="text-sm font-medium text-gray-700 mb-2">Preview:</p>
                            <div id="filePreview" class="inline-block p-3 border border-gray-300 rounded-lg bg-gray-50"></div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="text-center mt-8 pt-4 border-t">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded text-sm font-medium transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>Kirim Request
                    </button>
                    <a href="{{ route('messenger.index') }}" class="ml-2 bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded text-sm font-medium transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('messengerForm');
    const fileInput = document.getElementById('foto_barang');
    const previewContainer = document.getElementById('previewContainer');
    const filePreview = document.getElementById('filePreview');
    const uploadArea = fileInput.closest('.border-dashed');
    const phoneInput = document.getElementById('no_hp_penerima');

    // Click handler untuk area upload
    uploadArea.addEventListener('click', function() {
        fileInput.click();
    });

    // Drag and drop handlers
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
        uploadArea.classList.add('border-blue-500', 'bg-blue-50');
    }

    function unhighlight() {
        uploadArea.classList.remove('border-blue-500', 'bg-blue-50');
    }

    // Handle file drop
    uploadArea.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        handleFileUpload(files[0]);
    });

    // Handle file selection via click
    fileInput.addEventListener('change', function(e) {
        if (this.files.length > 0) {
            handleFileUpload(this.files[0]);
        }
    });

    // Fungsi untuk handle file upload
    function handleFileUpload(file) {
        // Validasi ukuran file (20MB)
        const maxSize = 20 * 1024 * 1024;
        if (file.size > maxSize) {
            alert(`File terlalu besar! Maksimal 20MB. File Anda: ${(file.size / (1024 * 1024)).toFixed(2)}MB`);
            fileInput.value = '';
            previewContainer.classList.add('hidden');
            return;
        }

        // Validasi tipe file
        const validTypes = [
            'image/jpeg', 'image/jpg', 'image/png',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        if (!validTypes.includes(file.type)) {
            alert('Format file tidak valid. Harap unggah JPG, PNG, PDF, DOC, atau DOCX.');
            fileInput.value = '';
            previewContainer.classList.add('hidden');
            return;
        }

        // Create preview
        let previewContent = '';
        if (file.type.startsWith('image/')) {
            previewContent = `
                <div class="flex items-center space-x-4">
                    <img src="${URL.createObjectURL(file)}" class="h-32 w-32 object-cover rounded" alt="Preview">
                    <div>
                        <p class="font-medium text-sm">${file.name}</p>
                        <p class="text-xs text-gray-500">${(file.size / 1024).toFixed(2)} KB</p>
                    </div>
                </div>
            `;
        } else if (file.type === 'application/pdf') {
            previewContent = `
                <div class="flex items-center space-x-4">
                    <div class="bg-red-100 p-3 rounded">
                        <i class="fas fa-file-pdf text-3xl text-red-500"></i>
                    </div>
                    <div>
                        <p class="font-medium text-sm">${file.name}</p>
                        <p class="text-xs text-gray-500">PDF Document • ${(file.size / 1024).toFixed(2)} KB</p>
                    </div>
                </div>
            `;
        } else if (file.type.includes('document')) {
            previewContent = `
                <div class="flex items-center space-x-4">
                    <div class="bg-blue-100 p-3 rounded">
                        <i class="fas fa-file-word text-3xl text-blue-500"></i>
                    </div>
                    <div>
                        <p class="font-medium text-sm">${file.name}</p>
                        <p class="text-xs text-gray-500">Word Document • ${(file.size / 1024).toFixed(2)} KB</p>
                    </div>
                </div>
            `;
        }

        filePreview.innerHTML = previewContent;
        previewContainer.classList.remove('hidden');
    }

    // Form validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        let errorMessages = [];

        // Validasi phone number
        const phonePattern = /^[0-9]{10,13}$/;
        if (!phonePattern.test(phoneInput.value.trim())) {
            errorMessages.push('Nomor HP Penerima harus 10-13 digit angka');
            phoneInput.classList.add('border-red-500');
            isValid = false;
        } else {
            phoneInput.classList.remove('border-red-500');
        }

        // Validasi file
        if (!fileInput.files.length) {
            errorMessages.push('Foto/Dokumen Barang wajib diupload');
            uploadArea.classList.add('border-red-500');
            isValid = false;
        } else {
            uploadArea.classList.remove('border-red-500');
            
            // Validasi ukuran file
            const file = fileInput.files[0];
            const maxSize = 20 * 1024 * 1024;
            if (file.size > maxSize) {
                errorMessages.push(`File terlalu besar! Maksimal 20MB`);
                isValid = false;
            }
        }

        // Validasi semua required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim() && field.id !== 'foto_barang') {
                field.classList.add('border-red-500');
                isValid = false;
            } else {
                field.classList.remove('border-red-500');
            }
        });

        if (!isValid) {
            e.preventDefault();
            if (errorMessages.length > 0) {
                alert('Terjadi kesalahan:\n\n' + errorMessages.join('\n'));
            }
            return false;
        }

        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim...';
        submitBtn.disabled = true;

        // Re-enable button jika form tidak terkirim (misal ada error validasi server)
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 5000);
    });

    // Remove error styling on input
    form.querySelectorAll('input, textarea, select').forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('border-red-500');
        });
    });
});
</script>
@endsection
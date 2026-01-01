@extends('layouts.public')

@section('title', 'Standar Pelayanan Fasilitas Bantuan Teknis')

@section('content')
    <div class="mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center mb-4">
            STANDAR PELAYANAN FASILITAS BANTUAN TEKNIS
        </h1>
        <p class="text-gray-700 px-8 text-center mb-12">
            Pelayanan fasilitas bantuan teknis di Kantor Bahasa Provinsi Jambi adalah layanan yang menyediakan dukungan atau
            bantuan terkait teknis kebahasaan, seperti bimbingan penggunaan bahasa Indonesia yang baik dan benar, konsultasi
            linguistik, dan pelatihan bahasa bagi masyarakat, instansi, atau lembaga di Provinsi Jambi.
        </p>

        <div class="grid grid-row-1 md:grid-row-2 gap-8">

            <div class="rounded-lg shadow-md overflow-hidden flex justify-center items-center">
                <img class="expandable-image w-124 h-auto cursor-pointer hover:opacity-80 transition-opacity"
                    src="{{ asset('images/stapel/3.png') }}" alt="Standar Pelayanan Perpustakaan Bagian 1">
            </div>
        </div>
    </div>


    <div id="image-modal" class="image-modal-overlay">
        <span class="image-modal-close">&times;</span>
        <img class="image-modal-content" id="modal-image-content">
    </div>

    @push('styles')
        <style>
            .image-modal-overlay {
                display: none;
                position: fixed;
                z-index: 9999;
                padding-top: 50px;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0, 0, 0, .9)
            }

            .image-modal-content {
                margin: auto;
                display: block;
                max-width: 90%;
                max-height: 90vh
            }

            .image-modal-close {
                position: absolute;
                top: 15px;
                right: 35px;
                color: #f1f1f1;
                font-size: 40px;
                font-weight: 700;
                transition: .3s;
                cursor: pointer
            }

            .image-modal-close:focus,
            .image-modal-close:hover {
                color: #bbb;
                text-decoration: none
            }
        </style>
    @endpush


    @push('scripts')
        <script>
            const modal = document.getElementById("image-modal"),
                modalImg = document.getElementById("modal-image-content"),
                closeBtn = document.querySelector(".image-modal-close"),
                images = document.querySelectorAll(".expandable-image");
            images.forEach(e => {
                e.onclick = function() {
                    modal.style.display = "block", modalImg.src = this.src
                }
            }), closeBtn.onclick = function() {
                modal.style.display = "none"
            }, window.onclick = function(e) {
                e.target == modal && (modal.style.display = "none")
            };
        </script>
    @endpush


@endsection

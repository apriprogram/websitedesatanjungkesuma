@extends('admin.layouts.app')

@section('title', 'Informasi Publik')

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-residents.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-references.css') }}">
    <style>
        .pi-shell {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .pi-block {
            background: #fff;
            border-radius: 22px;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: none;
            padding: 1.25rem 1.35rem;
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }
        body.dark-mode .pi-block {
            background: #0f172a;
            border-color: #1f2937;
            box-shadow: 0 18px 44px rgba(0, 0, 0, 0.35);
        }

        .pi-block.section-main:hover {
            border-color: #3FB7DC;
            box-shadow: 0 0 0 2px rgba(63, 183, 220, 0.25), 0 18px 44px rgba(63, 183, 220, 0.25);
        }

        .pi-block.section-request:hover {
            border-color: #2DC86D;
            box-shadow: 0 0 0 2px rgba(45, 200, 109, 0.25), 0 18px 44px rgba(45, 200, 109, 0.25);
        }

        .pi-block.section-hours:hover {
            border-color: #FF7052;
            box-shadow: 0 0 0 2px rgba(255, 112, 82, 0.25), 0 18px 44px rgba(255, 112, 82, 0.25);
        }

        .pi-block.section-map:hover,
        .pi-block.section-links:hover {
            border-color: #FFC803;
            box-shadow: 0 0 0 2px rgba(255, 200, 3, 0.25), 0 18px 44px rgba(255, 200, 3, 0.25);
        }

        .pi-block__header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            padding: 0.85rem 0.9rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
            margin: -1.25rem -1.35rem 1rem;
            border-radius: 21px 21px 0 0;
            color: #0f172a;
        }

        .pi-block__header.section-main {
            background: #ECF7FB;
            color: #0f172a;
        }

        .pi-block__header.section-request {
            background: #E9F9EF;
            color: #0f172a;
        }

        .pi-block__header.section-hours {
            background: #FFF0ED;
            color: #0f172a;
        }

        .pi-block__header.section-map,
        .pi-block__header.section-links {
            background: #FFF9E6;
            color: #0f172a;
        }
        body.dark-mode .pi-block__header {
            border-color: #1f2937;
            color: #e5e7eb;
        }
        body.dark-mode .pi-block__header.section-main {
            background: rgba(63, 183, 220, 0.12);
        }
        body.dark-mode .pi-block__header.section-request {
            background: rgba(45, 200, 109, 0.12);
        }
        body.dark-mode .pi-block__header.section-hours {
            background: rgba(255, 112, 82, 0.12);
        }
        body.dark-mode .pi-block__header.section-map,
        body.dark-mode .pi-block__header.section-links {
            background: rgba(255, 200, 3, 0.12);
        }

        .pi-block__title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: inherit;
            letter-spacing: 0.01em;
        }

        .pi-block__description {
            margin: 0.2rem 0 0;
            color: rgba(15, 23, 42, 0.78);
            font-size: 0.95rem;
        }

        .pi-block__body {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .pi-form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 0.85rem 1rem;
        }

        .pi-form-grid--wide {
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        }

        .pi-form-grid .full {
            grid-column: 1 / -1;
        }

        .pi-field {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            font-weight: 600;
            color: #0f172a;
        }
        body.dark-mode .pi-field {
            color: #e5e7eb;
        }

        .pi-input,
        .pi-textarea,
        .pi-select {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 0.7rem 0.85rem;
            font-size: 0.95rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            font-family: 'Poppins', 'Inter', system-ui, sans-serif;
        }
        body.dark-mode .pi-input,
        body.dark-mode .pi-textarea,
        body.dark-mode .pi-select {
            background: #0b1221;
            border-color: #1f2937;
            color: #e5e7eb;
        }

        .pi-input:focus,
        .pi-textarea:focus,
        .pi-select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.16);
        }
        body.dark-mode .pi-input:focus,
        body.dark-mode .pi-textarea:focus,
        body.dark-mode .pi-select:focus {
            border-color: rgba(96, 165, 250, 0.8);
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
            background: #0f172a;
        }

        .pi-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .pi-preview {
            margin-top: 0.35rem;
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .pi-preview img {
            max-width: 220px;
            border-radius: 14px;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08);
            background: #f9fafb;
        }
        body.dark-mode .pi-preview img {
            border-color: #1f2937;
            box-shadow: 0 12px 26px rgba(0, 0, 0, 0.4);
            background: #0b1221;
        }

        .request-layout {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(220px, 1fr);
            gap: 1rem;
            align-items: start;
        }

        .request-preview-box {
            background: #f8fafc;
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 14px;
            padding: 0.75rem;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 220px;
        }
        body.dark-mode .request-preview-box {
            background: #0f172a;
            border-color: #1f2937;
        }

        .request-preview-box img {
            width: 100%;
            height: auto;
            max-height: 320px;
            object-fit: cover;
            border-radius: 12px;
        }

        @media (max-width: 960px) {
            .request-layout {
                grid-template-columns: 1fr;
            }

            .request-preview-box {
                min-height: 180px;
            }
        }

        .map-layout {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr);
            gap: 1rem;
            align-items: start;
        }

        .map-preview-box {
            background: #f8fafc;
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 14px;
            padding: 0.75rem;
            min-height: 500px;
            display: grid;
            place-items: center;
        }
        body.dark-mode .map-preview-box {
            background: #0f172a;
            border-color: #1f2937;
        }

        .map-preview-box iframe {
            width: 100% !important;
            height: 500px !important;
            border: 0;
            border-radius: 10px;
        }

        .map-preview-placeholder {
            color: #94a3b8;
            text-align: center;
        }

        .map-embed-input {
            display: none;
        }

        @media (max-width: 960px) {
            .map-layout {
                grid-template-columns: 1fr;
            }
        }

        .pi-table-card {
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
        }
        body.dark-mode .pi-table-card {
            background: #0f172a;
            border-color: #1f2937;
        }

        .pi-table {
            width: 100%;
            border-collapse: collapse;
        }

        .pi-table thead th {
            background: #f8fafc;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #475569;
            text-align: left;
        }
        body.dark-mode .pi-table thead th {
            background: #111827;
            color: #e2e8f0;
            border-color: #1f2937;
        }

        .pi-table th,
        .pi-table td {
            padding: 0.75rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
            vertical-align: middle;
        }
        body.dark-mode .pi-table th,
        body.dark-mode .pi-table td {
            border-color: #1f2937;
            color: #e5e7eb;
        }

        .pi-table tbody tr:hover {
            background: #FFF0ED;
        }
        body.dark-mode .pi-table tbody tr:hover {
            background: rgba(255, 112, 82, 0.08);
        }

        .pi-table tr.is-closed-row {
            background: #fff5f2;
        }
        body.dark-mode .pi-table tr.is-closed-row {
            background: rgba(255, 112, 82, 0.12);
        }

        .pi-table tr.is-closed-row input {
            background: #fff;
        }
        body.dark-mode .pi-table tr.is-closed-row input {
            background: #0b1221;
            border-color: #1f2937;
            color: #e5e7eb;
        }

        .pi-table tr:last-child td {
            border-bottom: none;
        }

        .pi-table input {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 0.55rem 0.6rem;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
        }
        body.dark-mode .pi-table input {
            background: #0b1221;
            border-color: #1f2937;
            color: #e5e7eb;
        }

        .pi-table input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12);
        }
        body.dark-mode .pi-table input:focus {
            border-color: rgba(96, 165, 250, 0.8);
            box-shadow: 0 0 0 2px rgba(96, 165, 250, 0.18);
        }

        .pi-switch {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .pi-switch input {
            appearance: none;
            width: 44px;
            height: 24px;
            border-radius: 999px;
            border: 1px solid #2563eb;
            background: #e0ecff;
            position: relative;
            cursor: pointer;
            transition: background 0.18s ease, border-color 0.18s ease;
        }
        body.dark-mode .pi-switch input {
            border-color: rgba(96, 165, 250, 0.75);
            background: rgba(30, 41, 59, 0.85);
        }

        .pi-switch input::after {
            content: "";
            position: absolute;
            top: 3px;
            left: 4px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #2563eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
            transition: transform 0.18s ease, background 0.18s ease;
        }
        body.dark-mode .pi-switch input::after {
            background: #60a5fa;
        }

        .pi-switch input:checked {
            background: #2563eb !important;
            border-color: #2563eb !important;
        }
        body.dark-mode .pi-switch input:checked {
            background: #60a5fa !important;
            border-color: #60a5fa !important;
        }

        .pi-switch input:checked::after {
            transform: translateX(18px);
            background: #fff !important;
        }

        .pi-switch.map-toggle input {
            border-color: #2563eb;
            background: #dbeafe;
        }

        .pi-switch.map-toggle input::after {
            background: #2563eb;
        }

        .pi-switch.map-toggle input:checked {
            background: #2563eb;
            border-color: #2563eb;
        }

        .pi-switch.map-toggle input:checked::after {
            background: #fff;
        }

        .pi-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        .pi-actions .checkbox {
            margin-right: auto;
        }

        .pi-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 12px;
            padding: 0.65rem 1.2rem;
            height: 44px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #0f172a;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.18s ease, color 0.18s ease, border-color 0.18s ease;
            box-shadow: none;
            font-family: "Poppins", "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .pi-btn i {
            font-size: 0.95rem;
        }

        .pi-btn[data-variant="main"] {
            background: #3FB7DC;
            border-color: #3FB7DC;
            color: #fff;
        }

        .pi-btn[data-variant="main"]:hover {
            background: #2fa5ca;
            border-color: #2fa5ca;
            color: #fff;
        }

        .pi-btn[data-variant="request"] {
            background: #2DC86D;
            border-color: #2DC86D;
            color: #fff;
        }

        .pi-btn[data-variant="request"]:hover {
            background: #26b861;
            border-color: #26b861;
            color: #fff;
        }

        .pi-btn[data-variant="hours"] {
            background: #FF7052;
            border-color: #FF7052;
            color: #fff;
        }

        .pi-btn[data-variant="hours"]:hover {
            background: #e85f43;
            border-color: #e85f43;
            color: #fff;
        }

        .pi-btn[data-variant="map"] {
            background: #FFC803;
            border-color: #FFC803;
            color: #0f172a;
        }

        .pi-btn[data-variant="map"]:hover {
            background: #e6b400;
            border-color: #e6b400;
            color: #0b1f2a;
        }

        .upload-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.6rem 1rem;
            background: #eef2ff;
            color: #1d4ed8;
            border: 1px dashed #c7d2fe;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease;
            width: fit-content;
        }

        .upload-btn:hover {
            background: #e0e7ff;
            border-color: #a5b4fc;
            color: #1e3a8a;
        }

        .pi-muted {
            color: #6b7280;
            font-size: 0.9rem;
            margin: 0;
        }

        .pi-inline {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .pi-divider {
            height: 1px;
            background: rgba(226, 232, 240, 0.9);
            margin: 0.5rem 0 1rem;
        }

        /* Dark mode tweaks */
        body.dark-mode .pi-block {
            background: #0f172a;
            border-color: #1f2937;
            box-shadow: 0 18px 44px rgba(0, 0, 0, 0.35);
        }
        body.dark-mode .pi-block__title {
            color: inherit;
        }
        body.dark-mode .pi-block__description {
            color: inherit;
        }
        body.dark-mode .pi-field {
            color: #e5e7eb;
        }
        body.dark-mode .pi-btn {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.08);
            color: #e5e7eb;
        }
        body.dark-mode .pi-btn:hover {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }
        body.dark-mode .pi-btn--ghost {
            border-color: #475569;
            color: #e5e7eb;
            background: rgba(255, 255, 255, 0.03);
        }
        body.dark-mode .pi-btn.pi-links-remove {
            border-color: rgba(239, 68, 68, 0.55);
            color: #fecdd3;
            background: rgba(239, 68, 68, 0.08);
        }

        body.dark-mode .pi-input,
        body.dark-mode .pi-textarea,
        body.dark-mode .pi-select {
            background: #0f172a;
            border-color: rgba(99, 102, 241, 0.35);
            color: #e5e7eb;
        }

        body.dark-mode .pi-input:focus,
        body.dark-mode .pi-textarea:focus,
        body.dark-mode .pi-select:focus {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.35);
        }

        body.dark-mode .pi-table-card {
            background: #0f172a;
            border-color: rgba(99, 102, 241, 0.25);
        }

        body.dark-mode .pi-table th,
        body.dark-mode .pi-table td {
            color: #e5e7eb;
            border-color: rgba(99, 102, 241, 0.2);
        }

        body.dark-mode .pi-table thead th {
            background: #0b1224;
            color: #c7d2fe;
        }

        body.dark-mode .pi-table tbody tr:hover {
            background: #141c30;
        }

        body.dark-mode .pi-table tbody tr {
            background: #0f172a;
        }

        body.dark-mode .pi-table tr.is-closed-row {
            background: #1c0f12;
        }

        body.dark-mode .pi-table tr.is-closed-row input {
            background: #0f172a;
            color: #e5e7eb;
            border-color: rgba(99, 102, 241, 0.35);
        }

        body.dark-mode .pi-table input {
            background: #0f172a;
            color: #e5e7eb;
            border-color: rgba(99, 102, 241, 0.3);
        }

        body.dark-mode .pi-table input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.35);
        }

        /* Header section-request tetap warna light mode di dark mode */
        body.dark-mode .pi-block__header.section-request {
            background: #E9F9EF;
            color: #0f172a;
        }

        body.dark-mode .request-preview-box {
            background: #111827;
            border-color: rgba(99, 102, 241, 0.35);
        }

        body.dark-mode .pi-actions .pi-btn {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.08);
            color: #e5e7eb;
        }

        body.dark-mode .pi-actions .pi-btn:hover {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        .map-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .map-modal.is-open {
            display: flex;
            animation: modalFadeIn 0.2s ease;
        }

        .map-modal__overlay {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
        }

        .map-modal__dialog {
            position: relative;
            background: #fff;
            border-radius: 18px;
            padding: 1.25rem;
            max-width: 820px;
            width: 92vw;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.2);
            display: grid;
            gap: 0.9rem;
            z-index: 1;
            animation: modalSlideUp 0.25s ease;
        }

        .map-modal__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
        }

        .map-modal__close {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            cursor: pointer;
        }

        .map-modal__body textarea {
            width: 100%;
            min-height: 220px;
            margin-bottom: 1rem;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 0.9rem;
            font-family: 'Poppins', 'Inter', system-ui, sans-serif;
        }

        .map-modal__actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        .map-modal__actions .pi-btn {
            height: 40px;
            padding: 0.5rem 1rem;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes modalSlideUp {
            from {
                transform: translateY(12px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        body.dark-mode .map-modal__dialog {
            background: #0f172a;
            border: 1px solid rgba(99, 102, 241, 0.25);
            color: #e5e7eb;
        }

        body.dark-mode .map-modal__header h3 {
            color: #e5e7eb;
        }

        body.dark-mode .map-modal__close {
            background: #111827;
            border-color: rgba(99, 102, 241, 0.35);
            color: #e5e7eb;
        }

        body.dark-mode .map-modal__body textarea {
            background: #111827;
            color: #e5e7eb;
            border-color: rgba(99, 102, 241, 0.35);
        }

        @media (max-width: 720px) {
            .pi-block {
                padding: 1.05rem 1.1rem;
                border-radius: 18px;
            }

            .pi-block__header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endpush

@section('content')
    @include('admin.partials.alerts')

    <header class="main-header">
        <div class="header-controls">
            <div class="header-cluster header-cluster-left">
                <button class="header-icon sidebar-collapse-btn" id="sidebarCollapseBtn" aria-label="Sembunyikan sidebar">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="header-icon sidebar-trigger" id="sidebarExpandBtn" aria-label="Tampilkan sidebar">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <span class="header-title-text">Informasi Publik</span>
            @include('admin.partials.header-controls')
        </div>
        <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
            <span>Dashboard</span>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <span>Publikasi</span>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <span>Informasi Publik</span>
        </nav>
    </header>

    <section class="page-title page-title--with-actions">
        <div>
            <h1>Informasi Publik</h1>
            <p>Atur konten kartu permohonan, jam kerja, dan peta kantor desa yang tampil di beranda.</p>
        </div>
    </section>

    <div class="pi-shell">
        <form method="POST" action="{{ route('admin.public-info.update') }}" class="pi-block section-main"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="update_section" value="main">
            <div class="pi-block__header section-main">
                <div>
                    <h3 class="pi-block__title"><i class="fas fa-info-circle"></i> Section Informasi Publik</h3>
                    <p class="pi-block__description">Judul & subjudul yang tampil di beranda.</p>
                </div>
            </div>
            <div class="pi-block__body">
                <div class="pi-form-grid">
                    <label class="pi-field full">
                        <span>Judul Section</span>
                        <input class="pi-input" type="text" name="section_title"
                            value="{{ old('section_title', $setting->section_title) }}" required>
                    </label>
                    <label class="pi-field full">
                        <span>Subjudul Section</span>
                        <input class="pi-input" type="text" name="section_subtitle"
                            value="{{ old('section_subtitle', $setting->section_subtitle) }}">
                    </label>
                    <label class="pi-field full">
                        <span>Nomor WhatsApp (Widget)</span>
                        <input class="pi-input" type="text" name="whatsapp_number"
                            value="{{ old('whatsapp_number', $setting->whatsapp_number) }}" 
                            placeholder="6281234567890">
                        <small class="pi-muted">Gunakan format angka saja (mis: 6281234567890). Ini akan digunakan untuk widget WhatsApp di pojok halaman.</small>
                    </label>
                </div>
                <div class="pi-actions">
                    <button type="submit" class="pi-btn" data-variant="main"><i class="fas fa-save"></i><span>Simpan
                            Section</span></button>
                </div>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.public-info.update') }}" class="pi-block section-request"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="update_section" value="request">
            <div class="pi-block__header section-request">
                <div>
                    <h3 class="pi-block__title"><i class="fas fa-file-alt"></i> Permohonan Informasi Publik</h3>
                    <p class="pi-block__description">Konten kartu permohonan (judul, deskripsi, tombol, gambar).</p>
                </div>
            </div>
            <div class="pi-block__body">
                <div class="request-layout">
                    <div class="request-form">
                        <div class="pi-form-grid">
                            <label class="pi-field full">
                                <span>Judul Permohonan</span>
                                <input class="pi-input" type="text" name="request_title"
                                    value="{{ old('request_title', $setting->request_title) }}">
                            </label>
                            <label class="pi-field">
                                <span>Teks Tombol</span>
                                <input class="pi-input" type="text" name="request_button_label"
                                    value="{{ old('request_button_label', $setting->request_button_label) }}">
                            </label>
                            <label class="pi-field">
                                <span>URL Tombol</span>
                                <input class="pi-input" type="url" name="request_button_url"
                                    value="{{ old('request_button_url', $setting->request_button_url) }}"
                                    placeholder="https://">
                            </label>
                            <label class="pi-field">
                                <span>Gambar (opsional)</span>
                                <label class="upload-btn">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Pilih gambar</span>
                                    <input class="pi-input" type="file" name="request_image" accept="image/*" hidden>
                                </label>
                                <small class="pi-muted">Disarankan rasio 16:9, ukuran maks 2 MB.</small>
                            </label>
                        </div>
                        <div class="pi-form-grid pi-form-grid--wide">
                            <label class="pi-field full">
                                <span>Deskripsi Permohonan</span>
                                <textarea class="pi-textarea" name="request_description"
                                    rows="3">{{ old('request_description', $setting->request_description) }}</textarea>
                            </label>
                        </div>
                    </div>
                    <div class="request-preview">
                        <div class="request-preview-box" id="requestImagePreviewWrap">
                            @if($setting->request_image)
                                @php
                                    $reqImg = $setting->request_image;
                                    $reqImgUrl = Str::startsWith($reqImg, ['http://', 'https://'])
                                        ? $reqImg
                                        : asset('storage/' . ltrim($reqImg, '/'));
                                @endphp
                                <img id="requestImagePreview" src="{{ $reqImgUrl }}" alt="Gambar permohonan" loading="lazy">
                            @else
                                <img id="requestImagePreview" src="" alt="" style="display:none;">
                            @endif
                        </div>
                        <small
                            class="pi-muted">{{ $setting->request_image ? 'Gambar saat ini' : 'Belum ada gambar' }}</small>
                    </div>
                </div>
                <div class="pi-actions">
                    <button type="submit" class="pi-btn" data-variant="request"><i
                            class="fas fa-paper-plane"></i><span>Simpan Permohonan</span></button>
                </div>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.public-info.update') }}" class="pi-block section-hours">
            @csrf
            @method('PUT')
            <input type="hidden" name="update_section" value="hours">
            <div class="pi-block__header section-hours">
                <div>
                    <h3 class="pi-block__title"><i class="fas fa-business-time"></i> Jam Kerja</h3>
                    <p class="pi-block__description">Isi jam buka/tutup per hari atau tandai libur.</p>
                </div>
            </div>
            <div class="pi-block__body">
                <div class="pi-table-card">
                    <table class="pi-table">
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <th>Buka</th>
                                <th>Tutup</th>
                                <th>Catatan</th>
                                <th>Libur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hours as $hour)
                                <tr>
                                    <td style="font-weight:600;">{{ $hour->day_label }}</td>
                                    <td>
                                        @php
                                            $openTime = old("hours.{$hour->day_of_week}.open_time", $hour->open_time);
                                            $openTime = str_replace('.', ':', $openTime);
                                        @endphp
                                        <input type="time" name="hours[{{ $hour->day_of_week }}][open_time]"
                                            value="{{ $openTime }}" placeholder="08:00">
                                    </td>
                                    <td>
                                        @php
                                            $closeTime = old("hours.{$hour->day_of_week}.close_time", $hour->close_time);
                                            $closeTime = str_replace('.', ':', $closeTime);
                                        @endphp
                                        <input type="time" name="hours[{{ $hour->day_of_week }}][close_time]"
                                            value="{{ $closeTime }}" placeholder="16:00">
                                    </td>
                                    <td>
                                        <input type="text" name="hours[{{ $hour->day_of_week }}][note]"
                                            value="{{ old("hours.{$hour->day_of_week}.note", $hour->note) }}"
                                            placeholder="Opsional">
                                    </td>
                                    <td style="text-align:center;">
                                        <label class="pi-switch">
                                            <input type="checkbox" name="hours[{{ $hour->day_of_week }}][is_closed]" {{ old("hours.{$hour->day_of_week}.is_closed", $hour->is_closed) ? 'checked' : '' }}>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pi-actions">
                    <button type="submit" class="pi-btn" data-variant="hours"><i class="fas fa-clock"></i><span>Simpan Jam
                            Kerja</span></button>
                </div>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.public-info.update') }}" class="pi-block section-map">
            @csrf
            @method('PUT')
            <input type="hidden" name="update_section" value="map">
            <div class="pi-block__header section-map">
                <div>
                    <h3 class="pi-block__title"><i class="fas fa-map-marked-alt"></i> Maps Lokasi Kantor Desa</h3>
                    <p class="pi-block__description">Judul, deskripsi, dan embed iframe peta.</p>
                </div>
            </div>
            <div class="pi-block__body">
                <div class="map-layout">
                    <div class="map-form">
                        <div class="pi-form-grid">
                            <label class="pi-field full">
                                <span>Catatan Jam Kerja</span>
                                <input class="pi-input" type="text" name="hours_note"
                                    value="{{ old('hours_note', $setting->hours_note) }}"
                                    placeholder="Buka Senin-Jumat, 08.00-16.00 WIB">
                            </label>

                            <label class="pi-field full">
                                <span>Judul Peta</span>
                                <input class="pi-input" type="text" name="map_title"
                                    value="{{ old('map_title', $setting->map_title) }}">
                            </label>
                            <label class="pi-field full">
                                <span>Deskripsi Peta</span>
                                <input class="pi-input" type="text" name="map_description"
                                    value="{{ old('map_description', $setting->map_description) }}">
                            </label>
                            <label class="pi-field full">
                                <span>Embed URL Peta (iframe)</span>
                                <div class="pi-actions" style="justify-content:flex-start; gap:0.5rem;">
                                    <button type="button" class="pi-btn" data-variant="map" id="openMapEmbedModal"><i
                                            class="fas fa-link"></i><span>Isi Embed Peta</span></button>
                                    <span class="pi-muted">Tempel embed Google Maps, lalu simpan.</span>
                                </div>
                                <textarea class="pi-textarea map-embed-input" name="map_embed_url" id="mapEmbedInput"
                                    rows="2"
                                    placeholder="Tempel embed dari Google Maps">{{ old('map_embed_url', $setting->map_embed_url) }}</textarea>
                            </label>
                            <label class="pi-field full">
                                <span>Alamat Footer (tampilkan di bagian Lokasi & Alamat)</span>
                                <textarea class="pi-textarea" name="footer_address" rows="2"
                                    placeholder="Masukkan alamat lengkap desa">{{ old('footer_address', $setting->footer_address) }}</textarea>
                            </label>
                        </div>
                        <div class="pi-actions">
                            <label class="checkbox"
                                style="margin-right:auto; display:flex; align-items:center; gap:0.5rem;">
                                <span>Tampilkan di beranda</span>
                                <span class="pi-switch map-toggle">
                                    <input type="checkbox" name="is_published" value="1" {{ $setting->is_published ? 'checked' : '' }}>
                                </span>
                            </label>
                            <button type="submit" class="pi-btn" data-variant="map"><i
                                    class="fas fa-map-marker-alt"></i><span>Simpan Maps</span></button>
                        </div>
                        <small class="pi-muted">Jika dimatikan, peta lokasi tidak akan tampil di beranda maupun
                            footer.</small>
                    </div>
                    <div class="map-preview">
                        <div class="map-preview-box" id="mapPreview">
                            @if($setting->map_embed_url)
                                {!! $setting->map_embed_url !!}
                            @else
                                <div class="map-preview-placeholder">Tempel embed peta untuk melihat pratinjau.</div>
                            @endif
                        </div>
                        <small class="pi-muted">Pratinjau embed peta.</small>
                    </div>
                </div>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.public-info.update') }}" class="pi-block section-links">
            @csrf
            @method('PUT')
            <input type="hidden" name="update_section" value="footer_links">
            <div class="pi-block__header section-map">
                <div>
                    <h3 class="pi-block__title"><i class="fas fa-link"></i> Tautan Footer</h3>
                    <p class="pi-block__description">Kelola link di kolom kiri footer (Profil, Pemerintahan, dst).</p>
                </div>
            </div>
            <div class="pi-block__body">
                <div class="pi-form-grid full">
                    <div class="pi-field full">
                        <span>Daftar Tautan</span>
                        <div id="footerLinksList" class="pi-links-list" style="display:flex;flex-direction:column;gap:10px;">
                            @php $links = old('footer_links', $footerLinks ?? []); @endphp
                            @forelse($links as $idx => $link)
                                <div class="pi-links-row" data-index="{{ $idx }}" style="display:grid;grid-template-columns:1fr 1fr auto;gap:8px;align-items:center;">
                                    <input class="pi-input" name="footer_links[{{ $idx }}][label]" placeholder="Judul (mis. Profil)" value="{{ $link['label'] ?? '' }}">
                                    <input class="pi-input" name="footer_links[{{ $idx }}][url]" placeholder="URL atau anchor (mis. #profil)" value="{{ $link['url'] ?? '' }}">
                                    <button type="button" class="pi-btn pi-btn--ghost pi-links-remove" aria-label="Hapus baris">Hapus</button>
                                </div>
                            @empty
                                <div class="pi-links-row" data-index="0" style="display:grid;grid-template-columns:1fr 1fr auto;gap:8px;align-items:center;">
                                    <input class="pi-input" name="footer_links[0][label]" placeholder="Judul (mis. Profil)">
                                    <input class="pi-input" name="footer_links[0][url]" placeholder="URL atau anchor (mis. #profil)">
                                    <button type="button" class="pi-btn pi-btn--ghost pi-links-remove" aria-label="Hapus baris">Hapus</button>
                                </div>
                            @endforelse
                        </div>
                        <div class="pi-actions" style="margin-top:8px;">
                            <button type="button" id="addFooterLink" class="pi-btn" data-variant="map"><i class="fas fa-plus"></i><span>Tambah Tautan</span></button>
                            <small class="pi-muted">Kosongkan baris untuk menghapus. Gunakan anchor (#profil) atau URL penuh.</small>
                        </div>
                    </div>
                </div>
                <div class="pi-actions">
                    <button type="submit" class="pi-btn" data-variant="map"><i class="fas fa-save"></i><span>Simpan Tautan Footer</span></button>
                </div>
            </div>
        </form>

    </div>

    @include('admin.public-info.map-embed-modal')

    <form id="logout-form" action="{{ route('logout') }}" method="POST" hidden>
        @csrf
    </form>

@push('scripts')
<script>
(function() {
    const listEl = document.getElementById('footerLinksList');
    const addBtn = document.getElementById('addFooterLink');
    if (!listEl || !addBtn) return;

    const buildRow = (index, label = '', url = '') => {
        const row = document.createElement('div');
        row.className = 'pi-links-row';
        row.dataset.index = index;
        row.style.display = 'grid';
        row.style.gridTemplateColumns = '1fr 1fr auto';
        row.style.gap = '8px';
        row.style.alignItems = 'center';

        row.innerHTML = `
            <input class="pi-input" name="footer_links[${index}][label]" placeholder="Judul (mis. Profil)" value="${label}">
            <input class="pi-input" name="footer_links[${index}][url]" placeholder="URL atau anchor (mis. #profil)" value="${url}">
            <button type="button" class="pi-btn pi-btn--ghost pi-links-remove" aria-label="Hapus baris">Hapus</button>
        `;
        return row;
    };

    const renumber = () => {
        listEl.querySelectorAll('.pi-links-row').forEach((row, idx) => {
            row.dataset.index = idx;
            const inputs = row.querySelectorAll('input');
            if (inputs[0]) inputs[0].name = `footer_links[${idx}][label]`;
            if (inputs[1]) inputs[1].name = `footer_links[${idx}][url]`;
        });
    };

    addBtn.addEventListener('click', () => {
        const next = listEl.querySelectorAll('.pi-links-row').length;
        listEl.appendChild(buildRow(next));
    });

    listEl.addEventListener('click', (e) => {
        if (e.target.classList.contains('pi-links-remove')) {
            const row = e.target.closest('.pi-links-row');
            if (row) {
                row.remove();
                renumber();
            }
        }
    });
})();
</script>
@endpush
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const fileInput = document.querySelector('input[name="request_image"]');
            const previewImg = document.getElementById('requestImagePreview');
            const previewWrap = document.getElementById('requestImagePreviewWrap');

            // File preview and input change handling
            if (fileInput && previewImg) {
                fileInput.addEventListener('change', () => {
                    const file = fileInput.files?.[0];
                    if (file) {
                        const url = URL.createObjectURL(file);
                        previewImg.src = url;
                        previewImg.style.display = 'block';
                        previewWrap?.querySelectorAll('small').forEach((el) => el.remove());
                    }
                });
            }

            // Handle libur toggle: disable times and flag row
            const toggleLibur = (checkbox) => {
                const row = checkbox.closest('tr');
                const openInput = row?.querySelector('input[name*="[open_time]"]');
                const closeInput = row?.querySelector('input[name*="[close_time]"]');
                const isClosed = checkbox.checked;

                if (openInput) {
                    openInput.disabled = isClosed;
                    if (isClosed) openInput.value = '';
                }
                if (closeInput) {
                    closeInput.disabled = isClosed;
                    if (isClosed) closeInput.value = '';
                }

                if (row) {
                    row.classList.toggle('is-closed-row', isClosed);
                }
            };

            document.querySelectorAll('.pi-switch input[name*="[is_closed]"]').forEach((cb) => {
                toggleLibur(cb);
                cb.addEventListener('change', () => toggleLibur(cb));
            });

            // Map embed modal + preview
            const mapEmbedInput = document.getElementById('mapEmbedInput');
            const mapPreview = document.getElementById('mapPreview');
            const openMapModalBtn = document.getElementById('openMapEmbedModal');
            const mapModal = document.getElementById('mapEmbedModal');
            const mapModalClose = mapModal?.querySelectorAll('[data-map-modal-close]');
            const mapModalForm = document.getElementById('mapEmbedForm');
            const mapModalField = document.getElementById('mapEmbedField');

            const renderMapPreview = (val) => {
                if (!mapPreview) return;
                if (val && val.trim().length > 0) {
                    mapPreview.innerHTML = val;
                } else {
                    mapPreview.innerHTML = '<div class="map-preview-placeholder">Tempel embed peta untuk melihat pratinjau.</div>';
                }
            };

            renderMapPreview(mapEmbedInput?.value || '');

            const openMapModal = () => {
                if (mapModalField && mapEmbedInput) mapModalField.value = mapEmbedInput.value;
                mapModal?.classList.add('is-open');
            };
            const closeMapModal = () => mapModal?.classList.remove('is-open');

            openMapModalBtn?.addEventListener('click', openMapModal);
            mapModalClose?.forEach((btn) => btn.addEventListener('click', closeMapModal));
            mapModal?.addEventListener('click', (e) => { if (e.target === mapModal) closeMapModal(); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMapModal(); });

            mapModalForm?.addEventListener('submit', (e) => {
                e.preventDefault();
                if (!mapModalField || !mapEmbedInput) return;
                mapEmbedInput.value = mapModalField.value;
                renderMapPreview(mapModalField.value);
                closeMapModal();
            });
        });
    </script>
@endpush

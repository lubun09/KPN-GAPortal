<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tanda Terima</title>

<style>
@page {
    size: A4;
    margin: 12mm;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10pt;
    color: #000;
    margin: 0;
    padding: 0;
}

/* ===== TICKET ===== */
.ticket {
    height: 136mm;                  /* FIX: 2 dalam 1 A4 */
    border: 1px dashed #000;
    border-radius: 10px;
    padding: 7mm 9mm;
    box-sizing: border-box;
    position: relative;
    page-break-inside: avoid;
}

/* jarak antar copy (tidak bikin page break) */
.ticket + .ticket {
    margin-top: 1mm;
}

/* ===== HEADER ===== */
.title {
    text-align: center;
    font-size: 13pt;
    letter-spacing: 2px;
    font-weight: bold;
    margin-bottom: 3mm;
}

.brand {
    position: absolute;
    top: 5mm;
    right: 9mm;
    font-size: 8pt;
}

/* ===== TABLE LAYOUT ===== */
.layout {
    width: 100%;
    border-collapse: collapse;
}

.layout td {
    vertical-align: top;
    padding: 1.5mm 2mm;
}

.left {
    width: 60%;
}

.right {
    width: 40%;
    text-align: right;
}

/* ===== BLOCK TEXT ===== */
.block {
    margin-top: 3mm;
    word-wrap: break-word;
    white-space: normal;
}

/* ===== SIGNATURE ===== */
.sign {
    width: 100%;
    margin-top: 7mm;
    table-layout: fixed;
}

.sign td {
    width: 50%;
    text-align: center;
    padding-top: 3mm;
}

.line {
    border-top: 1px solid #000;
    width: 65%;
    margin: 6mm auto 2mm;
}
</style>
</head>

<body>

<!-- ================= COPY 1 ================= -->
<div class="ticket">

    <div class="brand">DOKUMEN · by GA Portal</div>
    <div class="title">TANDA TERIMA</div>

    <table class="layout">
        <tr>
            <td class="left">
                <strong>No Dokumen :</strong> {{ $document->nomor_dokumen }}<br><br>
                <strong>Pengirim :</strong> {{ $document->pengirim->name ?? '-' }}

                <div class="block">
                    <strong>Judul Dokumen :</strong><br>
                    {{ $document->judul }}
                </div>

                <div class="block">
                    <strong>Keterangan :</strong><br>
                    {{ $document->keterangan ?? '-' }}
                </div>
            </td>

            <td class="right">
                <strong>Tanggal :</strong> {{ $document->created_at->format('d-m-Y') }}<br><br>
                <strong>Penerima :</strong> {{ $document->penerima->name ?? '-' }}
            </td>
        </tr>
    </table>

    <table class="sign">
        <tr>
            <td>
                Pengirim
                <div class="line"></div>
                {{ $document->pengirim->name ?? '-' }}
            </td>
            <td>
                Penerima
                <div class="line"></div>
                {{ $document->penerima->name ?? '-' }}
            </td>
        </tr>
    </table>

</div>

<!-- ================= COPY 2 ================= -->
<div class="ticket">

    <div class="brand">DOKUMEN · by GA Portal</div>
    <div class="title">TANDA TERIMA</div>

    <table class="layout">
        <tr>
            <td class="left">
                <strong>No Dokumen :</strong> {{ $document->nomor_dokumen }}<br><br>
                <strong>Pengirim :</strong> {{ $document->pengirim->name ?? '-' }}

                <div class="block">
                    <strong>Judul Dokumen :</strong><br>
                    {{ $document->judul }}
                </div>

                <div class="block">
                    <strong>Keterangan :</strong><br>
                    {{ $document->keterangan ?? '-' }}
                </div>
            </td>

            <td class="right">
                <strong>Tanggal :</strong> {{ $document->created_at->format('d-m-Y') }}<br><br>
                <strong>Penerima :</strong> {{ $document->penerima->name ?? '-' }}
            </td>
        </tr>
    </table>

    <table class="sign">
        <tr>
            <td>
                Pengirim
                <div class="line"></div>
                {{ $document->pengirim->name ?? '-' }}
            </td>
            <td>
                Penerima
                <div class="line"></div>
                {{ $document->penerima->name ?? '-' }}
            </td>
        </tr>
    </table>

</div>

</body>
</html>

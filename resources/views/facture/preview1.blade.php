<style>
    .title {
        line-height: 1.5;
        margin: 0.5;
    }

    .header-exped {
        text-align: right;
        color: #4a5568;
        width: 60%;
        line-height: 0em;
    }

    .header-about {
        text-align: right;
        color: #4a5568;
        line-height: 0em;
    }

    hr {
        border: 0.5px solid #e2e8f0;
    }

    .logo {
        height: 50px;
        border-radius: 0.375rem;
    }

    .sub-header .company {
        width: 70%;
    }

    .w-deuxtiers {
        width: 55%;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background-color: #CE9D13;
    }

    thead th {
        padding: 0.5rem 2rem;
        font-size: 0.83rem;
        color: white;
    }

    .qte {
        width: 0.2em;
    }

    tbody td {
        font-size: 1rem;
        color: #4a5568;
        font-weight: 100;
    }

    tbody td.text-center {
        text-align: center;
    }

    tbody td.text-right {
        text-align: right;
    }

    .text-right {
        text-align: right;
    }

    .summary {
        text-align: right;
        color: #4a5568;
        line-height: 0em;
    }

    .notes {
        line-height: 0em;
    }

    .notes-title {
        font-weight: bold;
        color: #4a5568;
    }

    h5 {
        color: #4a5568;
    }

    .notes h6 {
        color: #4a5568;
    }

    .footer-info {
        color: #4a5568;
        text-align: center;
        width: 100%;
        text-align: center;
        position: fixed;
        bottom: -70px;
        line-height: 0em;
        height: 110px;
    }

    .uppercase {
        text-transform: uppercase;
    }

    .condition {
        line-height: 1.5;
    }
</style>

<div class="container body">
    <!-- HEADER -->
    <table class="w-full">
        <tr>
            <td>
                <img src="{{ $digitalOcean }}/img/entreprises/{{ $invoice->logo ?? $invoice->logo }}"
                    alt="Logo-Organisme_de_Formation" class="logo">
            </td>
            <td class="header-exped">
                <h2>{{ $invoice->typeFacture }}</h2>
                <h5 class="title">{{ $invoice->name ? $invoice->name : 'Pas de nom' }}</h5>
                <h5>Adresse :
                    {{ $invoice->customer_addr_lot || $invoice->customer_addr_quartier
                        ? $invoice->customer_addr_lot . ' ' . $invoice->customer_addr_quartier
                        : 'pas d\'adresse' }}
                </h5>
                <h5>{{ $setting->nif_name }} : {{ $invoice->nif ? $invoice->nif : 'pas de numero nif' }} |
                    {{ $setting->stat_name }} :
                    {{ $invoice->stat ? $invoice->stat : 'pas de numero stat' }}</h5>
            </td>
        </tr>
    </table>
    <!-- FIN HEADER -->
    <hr>
    <table class="w-full">
        <tr>
            <td class="w-deuxtiers" style="line-height: 0em">
                <h4 class="title">
                    {{ $entreprise->etp_name ? $entreprise->etp_name : 'pas de nom d\'entreprise' }}</h4>
                <h5 class="title">
                    {{ $entreprise->etp_addr_lot || $entreprise->etp_addr_quartier
                        ? $entreprise->etp_addr_lot . ' ' . $entreprise->etp_addr_quartier
                        : '' }}
                </h5>
                <h5>{{ $entreprise->etp_ville }} {{ $entreprise->etp_addr_code_postal }}</h5>
                <h5>
                    {{ $entreprise->etp_nif ? $setting->nif_name . ' : ' . $entreprise->etp_nif : '' }}
                </h5>
                <h5>
                    {{ $entreprise->etp_stat ? $setting->stat_name . ' : ' . $entreprise->etp_stat : '' }}
                </h5>

            </td>
            <td>
                <table class="w-full">
                    <tr>
                        <td style="line-height: 0em">
                            <h5>Numéro de facture :</h5>
                            @if (!($invoice->idTypeFacture == 2))
                                <h5>Numéro B.C :</h5>
                            @endif
                            <h5>Date de facture :</h5>
                            <h5>{{ !($invoice->idTypeFacture == 2) ? 'Date de paiement :' : "Valable jusqu'au :" }}
                            </h5>
                            <h5>Montant due :</h5>
                        </td>
                        <td class="header-about">
                            <h5>{{ $invoice->invoice_number }}</h5>
                            @if ($invoice->idTypeFacture != 2)
                                <h5>
                                    @if ($invoice->idTypeFacture == 3)
                                        {{-- Pour les factures d'acompte' --}}
                                        {{ $acompteInfo->numero ?? $invoice->invoice_bc }}
                                    @elseif ($invoice->idTypeFacture == 4)
                                        {{-- Pour les factures de solde --}}
                                        {{ $soldeInfo->numero ?? $invoice->invoice_bc }}
                                    @else
                                        {{-- Pour les factures standard --}}
                                        {{ $invoice->invoice_bc }}
                                    @endif
                                </h5>
                            @endif
                            <h5>{{ \Carbon\Carbon::parse($invoice->invoice_date)->translatedFormat('d F Y') }}</h5>
                            <h5>{{ \Carbon\Carbon::parse($invoice->invoice_date_pm)->translatedFormat('d F Y') }}</h5>
                            <h5>Ar {{ number_format($invoice->invoice_total_amount, 0, ',', ' ') }}</h5>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- FIN SUB HEADER -->

    <!-- CONTENT -->
    <table>
        <thead>
            <tr>
                <th>Services</th>
                <th>Description</th>
                <th class="qte">Qté</th>
                <th>Unité</th>
                <th>P.U</th>
                <th>Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoiceDetails as $invoiceDetails)
                <tr class="hover:bg-gray-50" style="line-height: 1.5em">
                    <td style="font-size: 0.83em">
                        @if ($invoice->idTypeFacture == 3)
                            Acompte
                        @elseif ($invoice->idTypeFacture == 4)
                            {{ $invoiceDetails->item_service }}
                        @else
                            Projet : {{ $invoiceDetails->module_name }}
                        @endif
                    </td>
                    <td style="font-size: 0.83em">{{ $invoiceDetails->item_description }}</td>
                    <td style="font-size: 0.83em" class="text-center qte">{{ $invoiceDetails->item_qty }}</td>
                    <td style="font-size: 0.83em" class="text-center">{{ $invoiceDetails->unit_name }}</td>
                    <td style="font-size: 0.83em" class="text-center">
                        {{ $setting->symbol }}
                        {{ number_format($invoiceDetails->item_unit_price, 0, ',', ' ') }}
                    </td>
                    <td style="font-size: 0.83em" class="text-center">
                        {{ $setting->symbol }}
                        {{ number_format($invoiceDetails->item_total_price, 0, ',', ' ') }}
                    </td>
                </tr>
            @endforeach

            @foreach ($invoiceDetailsOther as $detail)
                <tr class="hover:bg-gray-50" style="line-height: 1.5em">
                    <td style="font-size: 0.83em">
                        {{ $detail->Frais }}
                    </td>
                    <td style="font-size: 0.83em">{{ $detail->item_description }}</td>
                    <td style="font-size: 0.83em" class="text-center">{{ $detail->item_qty }}</td>
                    <td style="font-size: 0.83em" class="text-center">{{ $detail->unit_name }}</td>
                    <td style="font-size: 0.83em" class="text-center">{{ $setting->symbol }}
                        {{ number_format($detail->item_unit_price, 0, ',', ' ') }}</td>
                    <td style="font-size: 0.83em" class="text-center">{{ $setting->symbol }}
                        {{ number_format($detail->item_total_price, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <hr>
    <div class="summary">
        <table class="w-full">
            <tr>
                <td style="width: 72%"></td>
                <td>
                    <table class="w-full">
                        <tr>
                            <td style="line-height: 0em">
                                <h5>Sous-total :</h5>
                                <h5>Réduction :</h5>
                                <h5>TVA 20% :</h5>
                                <hr>
                                <h5>Total :</h5>
                                <hr>
                            </td>

                            <td class="header-about">
                                <h5>{{ $setting->symbol }}
                                    {{ number_format($invoice->invoice_sub_total, 0, ',', ' ') }}</h5>

                                <h5>{{ $setting->symbol }}
                                    {{ number_format($invoice->invoice_reduction, 0, ',', ' ') }}</h5>

                                <h5>{{ $setting->symbol }}
                                    {{ number_format($invoice->invoice_tva, 0, ',', ' ') }}</h5>

                                <hr>
                                <h5>{{ $setting->symbol }}
                                    {{ number_format($invoice->invoice_total_amount, 0, ',', ' ') }}</h5>
                                <hr>
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
        </table>
    </div>
    <div class="">
        <h5>Arrête la présente facture à la somme de
            <span class="uppercase">{{ $invoice->invoice_letter }}</span> {{ $setting->symbol }}
        </h5>
    </div>
    <hr>
    <div class="notes">
        <h5 class="notes-title">Notes / Termes</h5>
        <h6>Paiement par {{ $invoice->pm_type_name }}</h6>
        @if ($invoice->idTypePm == 1)
            <h6>Veuillez libeller le chèque à l'ordre de "{{ $invoice->ba_titulaire }}"</h6>
            @if ($invoice->invoice_condition)
                <h6 class="condition">Condition de paiement : {{ $invoice->invoice_condition }}</h6>
            @endif
        @elseif ($invoice->idTypePm == 2)
            <h6>RIB</h6>
            <h6>Titulaire du compte {{ $invoice->ba_titulaire }}</h6>
            <h6>Banque {{ $invoice->ba_name }}
                {{ $invoice->ba_quartier }},
                {{ $invoice->ville_name }} {{ $invoice->vi_code_postal }}</h6>
            <h6>Compte N° {{ $invoice->ba_account_number }}</h6>
            @if ($invoice->invoice_condition)
                <h6 class="condition">Condition de paiement : {{ $invoice->invoice_condition }}</h6>
            @endif
        @else
            @if ($invoice->invoice_condition)
                <h6 class="condition">Condition de paiement : {{ $invoice->invoice_condition }}</h6>
            @endif
        @endif
    </div>
    <!-- FIN CONTENT 2 -->

    <div class="footer-info">
        <div>
            <h6>{{ $invoice->name ? $invoice->name : 'Pas de nom' }} | {{ $setting->nif_name }} :
                {{ $invoice->nif ? $invoice->nif : 'Pas de numero NIF' }} | {{ $setting->stat_name }} :
                {{ $invoice->stat ? $invoice->stat : 'Pas de numero STAT' }} | RCS :
                {{ $invoice->rcs ? $invoice->rcs : 'Pas de numero rcs' }}</h6>
            <h6>MAIL : {{ $invoice->mail ? $invoice->mail : 'Pas d\adresse email' }} | ADRESSE :
                {{ $invoice->customer_addr_lot || $invoice->customer_addr_quartier || $invoice->customer_addr_code_postal ? $invoice->customer_addr_lot . ' ' . $invoice->customer_addr_quartier . ' ' . $invoice->customer_addr_code_postal : 'pas d\'adresse' }}
                | TELEPHONE :
                {{ $invoice->phone ? $invoice->phone : 'pas de numero téléphone' }}</h6>
            <h6>SITE WEB : {{ $invoice->website ? $invoice->website : 'pas de lien site web' }}</h6>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const totalAmount = parseFloat($('#totalAmount').text().replace(/\s+/g, ''));
        const totaly = $('#totalToWord').text(numberToWords(totalAmount));
    });


    function numberToWords(n) {
        const units = ["", "un", "deux", "trois", "quatre", "cinq", "six", "sept", "huit", "neuf"];
        const teens = ["dix", "onze", "douze", "treize", "quatorze", "quinze", "seize", "dix-sept", "dix-huit",
            "dix-neuf"
        ];
        const tens = ["", "dix", "vingt", "trente", "quarante", "cinquante", "soixante", "soixante-dix", "quatre-vingt",
            "quatre-vingt-dix"
        ];

        if (n < 10) {
            return units[n];
        } else if (n < 20) {
            return teens[n - 10];
        } else if (n < 100) {
            let ten = Math.floor(n / 10);
            let unit = n % 10;

            if (ten === 7) { // Cas 70-79
                return tens[6] + (unit === 1 ? "-et-" : "-") + teens[unit];
            }
            if (ten === 9) { // Cas 90-99
                return tens[8] + (unit > 0 ? "-" + teens[unit] : "");
            }
            return tens[ten] + (unit === 1 && ten !== 8 ? "-et-" : (unit > 0 ? "-" : "")) + units[unit];
        } else if (n < 1000) {
            let hundred = Math.floor(n / 100);
            let rest = n % 100;
            return (hundred > 1 ? units[hundred] + " " : "") + "cent" + (hundred > 1 && rest === 0 ? "s" : "") +
                (rest > 0 ? " " + numberToWords(rest) : "");
        } else if (n < 1000000) {
            let thousand = Math.floor(n / 1000);
            let rest = n % 1000;
            return (thousand > 1 ? numberToWords(thousand) + " " : "") + "mille" + (rest > 0 ? " " + numberToWords(
                rest) : "");
        } else if (n < 1000000000) {
            let million = Math.floor(n / 1000000);
            let rest = n % 1000000;
            return numberToWords(million) + " million" + (rest > 0 ? " " + numberToWords(rest) : "");
        } else {
            let billion = Math.floor(n / 1000000000);
            let rest = n % 1000000000;
            return numberToWords(billion) + " milliard" + (rest > 0 ? " " + numberToWords(rest) : "");
        }
    }
</script>

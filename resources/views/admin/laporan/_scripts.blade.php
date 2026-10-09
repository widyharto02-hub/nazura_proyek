<script>
    $(document).ready(function() {
        $('.select-bulan').select2({
            theme: "bootstrap4",
            placeholder: "Semua Bulan",
            allowClear: true,
            minimumResultsForSearch: Infinity
        });

        $('.select-tahun').select2({
            theme: "bootstrap4",
            placeholder: "Semua Tahun",
            allowClear: true,
            minimumResultsForSearch: Infinity
        });

        if ($('#filter_lokasi').length) {
            $('.select-lokasi').select2({
                theme: "bootstrap4",
                placeholder: "Semua Lokasi",
                allowClear: true,
                minimumResultsForSearch: Infinity
            });

            $('.select-unit').select2({
                theme: "bootstrap4",
                placeholder: "Semua Unit",
                allowClear: true,
                minimumResultsForSearch: Infinity
            });
        }
    });

    $(function() {
        const headers = @json($headers);
        const dataUrl = @json($dataUrl);
        const exportUrlTemplate = @json($exportUrlTemplate);
        const hasLokasiFilter = @json($hasLokasiFilter);
        const getUnitsUrl = '{{ route("laporan.get-units") }}';

        function buildColumns(headerList) {
            return headerList.map(function(title, i) {
                const numericKeywords = ['Tanggal', 'Harga', 'Total', 'Sub', 'RAB', 'Sudah', 'Sisa',
                    'Real', 'Stok', 'Nilai', 'Nominal', 'Jumlah', 'No'
                ];
                const isNumeric = numericKeywords.some(function(w) {
                    return title.indexOf(w) !== -1;
                });

                return {
                    data: 'c' + i,
                    name: 'c' + i,
                    title: title,
                    className: (i === 0 || isNumeric) ? 'text-center' : ''
                };
            });
        }

        function renderThead() {
            $('#thead_laporan').html('');
            $.each(headers, function(i, title) {
                $('#thead_laporan').append('<th>' + title + '</th>');
            });
        }

        function currentParams() {
            var params = {
                tanggal: $('#filter_tanggal').val(),
                bulan: $('#filter_bulan').val(),
                tahun: $('#filter_tahun').val()
            };
            if (hasLokasiFilter) {
                params.lokasi = $('#filter_lokasi').val();
                params.unit = $('#filter_unit').val();
            }
            return params;
        }

        function updateExportLinks() {
            const excelParams = $.param($.extend({}, currentParams(), { format: 'excel' }));
            const pdfParams = $.param($.extend({}, currentParams(), { format: 'pdf' }));
            $('.btn-export-excel').attr('href', exportUrlTemplate.replace(':tab', 'x') + '?' + excelParams);
            $('.btn-export-pdf').attr('href', exportUrlTemplate.replace(':tab', 'x') + '?' + pdfParams);
        }

        function renderFooter(json) {
            const footer = json.footer;
            if (footer && footer.label) {
                $('#laporan_footer').removeClass('d-none').html('<span>' + footer.label + ' :</span> <span>' + footer.value + '</span>');
            } else {
                $('#laporan_footer').addClass('d-none').html('');
            }
        }

        function loadUnits(idLokasi, selectedUnit) {
            const $unit = $('#filter_unit');
            $unit.prop('disabled', true).empty().append('<option value="">Semua Unit</option>');

            if (!idLokasi) {
                $unit.prop('disabled', false).trigger('change.select2');
                return;
            }

            $.ajax({
                url: getUnitsUrl,
                type: 'GET',
                data: { id_lokasi: idLokasi },
                success: function(response) {
                    if (response.status === 'success' && response.data.length > 0) {
                        $.each(response.data, function(i, unit) {
                            const selected = (selectedUnit && unit.id_kavling == selectedUnit) ? 'selected' : '';
                            $unit.append('<option value="' + unit.id_kavling + '" ' + selected + '>' + unit.kode_kavling + '</option>');
                        });
                    }
                    $unit.prop('disabled', false).trigger('change.select2');
                },
                error: function() {
                    $unit.prop('disabled', false).trigger('change.select2');
                }
            });
        }

        renderThead();

        const table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            responsive: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            ajax: {
                url: dataUrl,
                data: function(d) {
                    d.tanggal = $('#filter_tanggal').val();
                    d.bulan = $('#filter_bulan').val();
                    d.tahun = $('#filter_tahun').val();
                    if (hasLokasiFilter) {
                        d.lokasi = $('#filter_lokasi').val();
                        d.unit = $('#filter_unit').val();
                    }
                }
            },
            columns: buildColumns(headers),
            initComplete: function(settings, json) {
                renderFooter(json);
            }
        });

        table.on('xhr.dt', function(e, settings, json) {
            renderFooter(json);
        });

        updateExportLinks();

        $('#filter_tanggal, #filter_bulan, #filter_tahun').on('change', function() {
            const tanggal = $('#filter_tanggal').val();
            const bulan = $('#filter_bulan').val();
            const tahun = $('#filter_tahun').val();

            if (tanggal) {
                if (bulan || tahun) {
                    $('#filter_bulan').val('').trigger('change.select2');
                    $('#filter_tahun').val('').trigger('change.select2');
                }
            } else if (bulan || tahun) {
                $('#filter_tanggal').val('');
            }

            table.ajax.reload();
            updateExportLinks();
        });

        if (hasLokasiFilter) {
            $('#filter_lokasi').on('change', function() {
                const idLokasi = $(this).val();
                loadUnits(idLokasi, null);
                table.ajax.reload();
                updateExportLinks();
            });

            $('#filter_unit').on('change', function() {
                table.ajax.reload();
                updateExportLinks();
            });
        }
    });
</script>

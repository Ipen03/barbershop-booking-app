import 'package:flutter/material.dart';
import 'api_service.dart';
import 'models.dart';
import 'package:intl/intl.dart';

class BookingScreen extends StatefulWidget {
  final User user;
  final Service initialService;

  const BookingScreen({
    super.key,
    required this.user,
    required this.initialService,
  });

  @override
  State<BookingScreen> createState() => _BookingScreenState();
}

class _BookingScreenState extends State<BookingScreen> {
  late Service selectedService;
  Barber? selectedBarber;
  DateTime selectedDate = DateTime.now().add(const Duration(days: 1)); // Default besok
  String? selectedTime;
  final _notesController = TextEditingController();

  List<Barber> barbers = [];
  bool isLoadingBarbers = true;
  bool isSubmitting = false;

  final List<String> timeSlots = [
    "09:00", "10:00", "11:00", "12:00",
    "13:00", "14:00", "15:00", "16:00",
    "17:00", "19:00", "20:00", "21:00"
  ];

  final currencyFormatter = NumberFormat.currency(
    locale: 'id_ID',
    symbol: 'Rp ',
    decimalDigits: 0,
  );

  @override
  void initState() {
    super.initState();
    selectedService = widget.initialService;
    _fetchBarbers();
  }

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _fetchBarbers() async {
    try {
      final list = await ApiService.getBarbers();
      setState(() {
        barbers = list;
        isLoadingBarbers = false;
        if (barbers.isNotEmpty) {
          selectedBarber = barbers[0]; // Default pilih barber pertama
        }
      });
    } catch (e) {
      setState(() {
        isLoadingBarbers = false;
      });
    }
  }

  Future<void> _selectDate(BuildContext context) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: selectedDate,
      firstDate: DateTime.now(), // Minimal hari ini
      lastDate: DateTime.now().add(const Duration(days: 30)), // Maksimal 30 hari ke depan
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.dark(
              primary: Colors.amber,
              onPrimary: Color(0xFF121212),
              surface: Color(0xFF1E1E1E),
              onSurface: Colors.white,
            ),
            textButtonTheme: TextButtonThemeData(
              style: TextButton.styleFrom(
                foregroundColor: Colors.amber,
              ),
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null && picked != selectedDate) {
      setState(() {
        selectedDate = picked;
      });
    }
  }

  Future<void> _submitBooking() async {
    if (selectedBarber == null) {
      _showSnackBar("Silakan pilih barber terlebih dahulu.", Colors.redAccent);
      return;
    }
    if (selectedTime == null) {
      _showSnackBar("Silakan pilih jam reservasi terlebih dahulu.", Colors.redAccent);
      return;
    }

    setState(() {
      isSubmitting = true;
    });

    final formattedDate = DateFormat('yyyy-MM-dd').format(selectedDate);

    final result = await ApiService.createBooking(
      userId: widget.user.id,
      serviceId: selectedService.id,
      barberId: selectedBarber!.id,
      bookingDate: formattedDate,
      bookingTime: selectedTime!,
      notes: _notesController.text.trim(),
    );

    setState(() {
      isSubmitting = false;
    });

    if (result['status'] == 'success') {
      if (mounted) {
        // Tampilkan dialog sukses
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (context) => AlertDialog(
            backgroundColor: const Color(0xFF1E1E1E),
            title: const Row(
              children: [
                Icon(Icons.check_circle, color: Colors.green),
                SizedBox(width: 8),
                Text("Reservasi Berhasil!", style: TextStyle(color: Colors.white)),
              ],
            ),
            content: Text(
              "Reservasi Anda untuk '${selectedService.name}' pada tanggal ${DateFormat('dd MMMM yyyy').format(selectedDate)} pukul $selectedTime telah berhasil dibuat.",
              style: const TextStyle(color: Colors.grey),
            ),
            actions: [
              ElevatedButton(
                onPressed: () {
                  Navigator.pop(context); // Tutup dialog
                  Navigator.pop(context, true); // Kembali ke Home dengan refresh flag
                },
                style: ElevatedButton.styleFrom(backgroundColor: Colors.amber),
                child: const Text("OK", style: TextStyle(color: Color(0xFF121212))),
              ),
            ],
          ),
        );
      }
    } else {
      if (mounted) {
        _showSnackBar(result['message'] ?? "Gagal melakukan reservasi.", Colors.redAccent);
      }
    }
  }

  void _showSnackBar(String message, Color bgColor) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: bgColor,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF121212),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1E1E1E),
        title: const Text("Reservasi Jadwal", style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.amber),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: isSubmitting
          ? const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  CircularProgressIndicator(color: Colors.amber),
                  SizedBox(height: 16),
                  Text("Membuat reservasi Anda...", style: TextStyle(color: Colors.white)),
                ],
              ),
            )
          : SingleChildScrollView(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // SELECTED SERVICE SUMMARY
                    Card(
                      color: const Color(0xFF1E1E1E),
                      child: Padding(
                        padding: const EdgeInsets.all(16.0),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text("Layanan Terpilih:", style: TextStyle(color: Colors.grey, fontSize: 12)),
                                const SizedBox(height: 4),
                                Text(
                                  selectedService.name,
                                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white),
                                ),
                                const SizedBox(height: 4),
                                Text("${selectedService.duration} Menit", style: const TextStyle(color: Colors.amber, fontSize: 12)),
                              ],
                            ),
                            Text(
                              currencyFormatter.format(selectedService.price),
                              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),

                    // SELECT BARBER
                    const Text(
                      "Pilih Pemotong Rambut (Barber)",
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white),
                    ),
                    const SizedBox(height: 12),
                    isLoadingBarbers
                        ? const Center(child: CircularProgressIndicator(color: Colors.amber))
                        : SizedBox(
                            height: 100,
                            child: ListView.builder(
                              scrollDirection: Axis.horizontal,
                              itemCount: barbers.length,
                              itemBuilder: (context, index) {
                                final barber = barbers[index];
                                final isSelected = selectedBarber?.id == barber.id;
                                final initial = barber.name.isNotEmpty ? barber.name[0] : 'B';

                                return GestureDetector(
                                  onTap: () {
                                    setState(() {
                                      selectedBarber = barber;
                                    });
                                  },
                                  child: Container(
                                    width: 100,
                                    margin: const EdgeInsets.only(right: 12),
                                    padding: const EdgeInsets.all(8),
                                    decoration: BoxDecoration(
                                      color: isSelected ? Colors.amber.withOpacity(0.1) : const Color(0xFF1E1E1E),
                                      borderRadius: BorderRadius.circular(12),
                                      border: Border.all(
                                        color: isSelected ? Colors.amber : Colors.transparent,
                                        width: 2,
                                      ),
                                    ),
                                    child: Column(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        CircleAvatar(
                                          radius: 20,
                                          backgroundColor: isSelected ? Colors.amber : Colors.grey[700],
                                          child: Text(
                                            initial,
                                            style: TextStyle(
                                              fontSize: 14,
                                              fontWeight: FontWeight.bold,
                                              color: isSelected ? const Color(0xFF121212) : Colors.white,
                                            ),
                                          ),
                                        ),
                                        const SizedBox(height: 6),
                                        Text(
                                          barber.name.split(' ')[0],
                                          style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ],
                                    ),
                                  ),
                                );
                              },
                            ),
                          ),
                    const SizedBox(height: 24),

                    // SELECT DATE
                    const Text(
                      "Pilih Tanggal Kunjungan",
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white),
                    ),
                    const SizedBox(height: 12),
                    InkWell(
                      onTap: () => _selectDate(context),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                        decoration: BoxDecoration(
                          color: const Color(0xFF1E1E1E),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.white12),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Row(
                              children: [
                                const Icon(Icons.calendar_today, color: Colors.amber, size: 20),
                                const SizedBox(width: 12),
                                Text(
                                  DateFormat('EEEE, dd MMMM yyyy').format(selectedDate),
                                  style: const TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.w500),
                                ),
                              ],
                            ),
                            const Icon(Icons.arrow_drop_down, color: Colors.amber),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),

                    // SELECT TIME SLOT
                    const Text(
                      "Pilih Jam Kunjungan",
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white),
                    ),
                    const SizedBox(height: 12),
                    GridView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: 4,
                        crossAxisSpacing: 10,
                        mainAxisSpacing: 10,
                        childAspectRatio: 2.2,
                      ),
                      itemCount: timeSlots.length,
                      itemBuilder: (context, index) {
                        final time = timeSlots[index];
                        final isSelected = selectedTime == time;

                        return GestureDetector(
                          onTap: () {
                            setState(() {
                              selectedTime = time;
                            });
                          },
                          child: Container(
                            alignment: Alignment.center,
                            decoration: BoxDecoration(
                              color: isSelected ? Colors.amber : const Color(0xFF1E1E1E),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(
                                color: isSelected ? Colors.amber : Colors.white12,
                              ),
                            ),
                            child: Text(
                              time,
                              style: TextStyle(
                                color: isSelected ? const Color(0xFF121212) : Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 13,
                              ),
                            ),
                          ),
                        );
                      },
                    ),
                    const SizedBox(height: 24),

                    // EXTRA NOTES
                    const Text(
                      "Catatan Tambahan (Opsional)",
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _notesController,
                      style: const TextStyle(color: Colors.white),
                      maxLines: 3,
                      decoration: InputDecoration(
                        hintText: "Contoh: Potongan rambut undercut, tolong dirapikan jenggot saja, dll.",
                        hintStyle: const TextStyle(color: Colors.grey, fontSize: 13),
                        fillColor: const Color(0xFF1E1E1E),
                        filled: true,
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(color: Colors.white12),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(color: Colors.amber, width: 2),
                        ),
                      ),
                    ),
                    const SizedBox(height: 36),

                    // CONFIRM BUTTON
                    ElevatedButton(
                      onPressed: _submitBooking,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.amber,
                        foregroundColor: const Color(0xFF121212),
                        minimumSize: const Size(double.infinity, 54),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        elevation: 4,
                      ),
                      child: const Text(
                        "KONFIRMASI RESERVASI",
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, letterSpacing: 1.1),
                      ),
                    ),
                    const SizedBox(height: 30),
                  ],
                ),
              ),
            ),
    );
  }
}

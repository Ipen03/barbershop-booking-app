import 'dart:convert';
import 'package:http/http.dart' as http;
import 'models.dart';

class ApiService {
  // BISA DIUBAH SESUAI IP ADDRESS LAPTOP ANDA (Cek via ipconfig di cmd)
  // Contoh: '192.168.1.15' atau '10.0.2.2' jika pakai emulator Android bawaan
  static String ipAddress = '192.168.1.8'; 

  static String get baseUrl => 'http://$ipAddress/barber_api/';

  // 1. REGISTER
  static Future<Map<String, dynamic>> register(
      String name, String email, String password, String phone) async {
    try {
      final response = await http.post(
        Uri.parse('${baseUrl}register.php'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'name': name,
          'email': email,
          'password': password,
          'phone': phone,
        }),
      );

      final data = jsonDecode(response.body);
      return data;
    } catch (e) {
      return {'status': 'error', 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  // 2. LOGIN
  static Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('${baseUrl}login.php'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'email': email,
          'password': password,
        }),
      );

      final data = jsonDecode(response.body);
      return data;
    } catch (e) {
      return {'status': 'error', 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  // 3. GET SERVICES
  static Future<List<Service>> getServices() async {
    try {
      final response = await http.get(Uri.parse('${baseUrl}get_services.php'));
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['status'] == 'success') {
          List list = data['data'];
          return list.map((item) => Service.fromJson(item)).toList();
        }
      }
      return [];
    } catch (e) {
      print("Error getServices: $e");
      return [];
    }
  }

  // 4. GET BARBERS
  static Future<List<Barber>> getBarbers() async {
    try {
      final response = await http.get(Uri.parse('${baseUrl}get_barbers.php'));
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['status'] == 'success') {
          List list = data['data'];
          return list.map((item) => Barber.fromJson(item)).toList();
        }
      }
      return [];
    } catch (e) {
      print("Error getBarbers: $e");
      return [];
    }
  }

  // 5. CREATE BOOKING
  static Future<Map<String, dynamic>> createBooking({
    required int userId,
    required int serviceId,
    required int barberId,
    required String bookingDate,
    required String bookingTime,
    required String notes,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('${baseUrl}create_booking.php'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'user_id': userId,
          'service_id': serviceId,
          'barber_id': barberId,
          'booking_date': bookingDate,
          'booking_time': bookingTime,
          'notes': notes,
        }),
      );

      final data = jsonDecode(response.body);
      return data;
    } catch (e) {
      return {'status': 'error', 'message': 'Gagal terhubung ke server: $e'};
    }
  }

  // 6. GET BOOKINGS (Riwayat)
  static Future<List<Booking>> getBookings(int userId) async {
    try {
      final response = await http.get(Uri.parse('${baseUrl}get_bookings.php?user_id=$userId'));
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['status'] == 'success') {
          List list = data['data'];
          return list.map((item) => Booking.fromJson(item)).toList();
        }
      }
      return [];
    } catch (e) {
      print("Error getBookings: $e");
      return [];
    }
  }
}

class User {
  final int id;
  final String name;
  final String email;
  final String phone;

  User({
    required this.id,
    required this.name,
    required this.email,
    required this.phone,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: int.parse(json['id'].toString()),
      name: json['name'].toString(),
      email: json['email'].toString(),
      phone: json['phone'].toString(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'phone': phone,
    };
  }
}

class Service {
  final int id;
  final String name;
  final int price;
  final int duration;
  final String description;

  Service({
    required this.id,
    required this.name,
    required this.price,
    required this.duration,
    required this.description,
  });

  factory Service.fromJson(Map<String, dynamic> json) {
    return Service(
      id: int.parse(json['id'].toString()),
      name: json['name'].toString(),
      price: int.parse(json['price'].toString()),
      duration: int.parse(json['duration'].toString()),
      description: json['description'].toString(),
    );
  }
}

class Barber {
  final int id;
  final String name;
  final double rating;
  final String photo;

  Barber({
    required this.id,
    required this.name,
    required this.rating,
    required this.photo,
  });

  factory Barber.fromJson(Map<String, dynamic> json) {
    return Barber(
      id: int.parse(json['id'].toString()),
      name: json['name'].toString(),
      rating: double.parse(json['rating'].toString()),
      photo: json['photo']?.toString() ?? '',
    );
  }
}

class Booking {
  final int id;
  final String bookingDate;
  final String bookingTime;
  final String status;
  final String notes;
  final String serviceName;
  final int servicePrice;
  final int serviceDuration;
  final String barberName;
  final double barberRating;

  Booking({
    required this.id,
    required this.bookingDate,
    required this.bookingTime,
    required this.status,
    required this.notes,
    required this.serviceName,
    required this.servicePrice,
    required this.serviceDuration,
    required this.barberName,
    required this.barberRating,
  });

  factory Booking.fromJson(Map<String, dynamic> json) {
    return Booking(
      id: int.parse(json['id'].toString()),
      bookingDate: json['booking_date'].toString(),
      bookingTime: json['booking_time'].toString(),
      status: json['status'].toString(),
      notes: json['notes']?.toString() ?? '',
      serviceName: json['service_name'].toString(),
      servicePrice: int.parse(json['service_price'].toString()),
      serviceDuration: int.parse(json['service_duration'].toString()),
      barberName: json['barber_name'].toString(),
      barberRating: double.parse(json['barber_rating'].toString()),
    );
  }
}

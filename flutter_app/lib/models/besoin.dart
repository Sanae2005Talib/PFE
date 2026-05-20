// Model pour un Besoin
class Besoin {
  final int id;
  final String title;
  final String description;
  final String status; // urgent, normal, satisfied
  final String? quantity;
  final String? location;
  final String? deadline;
  final String typeName; // Type de don (Vêtements, Nourriture, etc.)

  Besoin({
    required this.id,
    required this.title,
    required this.description,
    required this.status,
    this.quantity,
    this.location,
    this.deadline,
    required this.typeName,
  });

  // Créer Besoin depuis JSON (API)
  factory Besoin.fromJson(Map<String, dynamic> json) {
    return Besoin(
      id: json['id'] ?? 0,
      title: json['title'] ?? '',
      description: json['description'] ?? '',
      status: json['status'] ?? 'normal',
      quantity: json['quantity'],
      location: json['location'],
      deadline: json['deadline'],
      typeName: json['type_name'] ?? '',
    );
  }

  // Helper pour savoir si urgent
  bool get isUrgent => status == 'urgent';
}

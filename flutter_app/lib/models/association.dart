// Model pour une Association
class Association {
  final int id;
  final String associationName;
  final String? logo;
  final String? description;
  final String phone;
  final String address;
  final String? regionName;
  final int regionId;
  final int activeNeedsCount;

  Association({
    required this.id,
    required this.associationName,
    this.logo,
    this.description,
    required this.phone,
    required this.address,
    this.regionName,
    required this.regionId,
    this.activeNeedsCount = 0,
  });

  // Créer Association depuis JSON (API)
  factory Association.fromJson(Map<String, dynamic> json) {
    return Association(
      id: json['id'] ?? 0,
      associationName: json['association_name'] ?? '',
      logo: json['logo'],
      description: json['description'],
      phone: json['phone'] ?? '',
      address: json['address'] ?? '',
      regionName: json['region_name'],
      regionId: json['region_id'] ?? 0,
      activeNeedsCount: json['active_needs_count'] ?? 0,
    );
  }
}

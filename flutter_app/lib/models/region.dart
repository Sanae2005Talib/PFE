// Model pour une Région
class Region {
  final int id;
  final String name;

  Region({
    required this.id,
    required this.name,
  });

  // Créer Region depuis JSON (API)
  factory Region.fromJson(Map<String, dynamic> json) {
    return Region(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
    );
  }
}

// Model pour un Type de Don
class DonationType {
  final int id;
  final String name;

  DonationType({
    required this.id,
    required this.name,
  });

  // Créer DonationType depuis JSON (API)
  factory DonationType.fromJson(Map<String, dynamic> json) {
    return DonationType(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
    );
  }
}

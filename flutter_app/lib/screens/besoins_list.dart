import 'package:flutter/material.dart';
import '../models/association.dart';
import '../models/besoin.dart';
import '../models/region.dart';
import '../services/api_service.dart';

/// Liste des besoins d'une association
/// Affiche tous les besoins avec filtres par type de don
class BesoinsListScreen extends StatefulWidget {
  final Association association;

  const BesoinsListScreen({
    super.key,
    required this.association,
  });

  @override
  State<BesoinsListScreen> createState() => _BesoinsListScreenState();
}

class _BesoinsListScreenState extends State<BesoinsListScreen> {
  final ApiService _apiService = ApiService();
  
  // Données
  List<Besoin> _besoins = [];
  List<Besoin> _besoinsFiltered = [];
  List<DonationType> _donationTypes = [];
  
  // État
  bool _loading = true;
  
  // Filtre
  int? _selectedTypeId;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  /// Charger les données depuis l'API
  Future<void> _loadData() async {
    setState(() => _loading = true);
    
    // Charger besoins et types de dons en parallèle
    final besoins = await _apiService.getBesoins(widget.association.id);
    final types = await _apiService.getDonationTypes();
    
    setState(() {
      _besoins = besoins;
      _besoinsFiltered = besoins;
      _donationTypes = types;
      _loading = false;
    });
  }

  /// Appliquer le filtre par type de don
  void _applyFilter(int? typeId) {
    setState(() {
      _selectedTypeId = typeId;
      
      if (typeId == null) {
        // Afficher tous les besoins
        _besoinsFiltered = _besoins;
      } else {
        // Filtrer par type
        _besoinsFiltered = _besoins.where((besoin) {
          // Trouver le type correspondant
          final type = _donationTypes.firstWhere(
            (t) => t.name == besoin.typeName,
            orElse: () => DonationType(id: 0, name: ''),
          );
          return type.id == typeId;
        }).toList();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Besoins'),
      ),
      
      body: Column(
        children: [
          // Header avec nom association
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            color: Theme.of(context).primaryColor.withOpacity(0.1),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  widget.association.associationName,
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Theme.of(context).primaryColor,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  '${_besoins.length} besoin(s) au total',
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.grey[700],
                  ),
                ),
              ],
            ),
          ),
          
          // Filtre par type de don
          if (_donationTypes.isNotEmpty)
            Container(
              padding: const EdgeInsets.all(16),
              child: DropdownButtonFormField<int>(
                initialValue: _selectedTypeId,
                decoration: InputDecoration(
                  labelText: 'Filtrer par type de don',
                  prefixIcon: const Icon(Icons.category),
                  filled: true,
                  fillColor: Colors.white,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                items: [
                  const DropdownMenuItem(
                    value: null,
                    child: Text('Tous les types'),
                  ),
                  ..._donationTypes.map((type) => DropdownMenuItem(
                        value: type.id,
                        child: Text(type.name),
                      )),
                ],
                onChanged: _applyFilter,
              ),
            ),
          
          // Liste des besoins
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _besoinsFiltered.isEmpty
                    ? _buildEmptyState()
                    : RefreshIndicator(
                        onRefresh: _loadData,
                        child: ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: _besoinsFiltered.length,
                          itemBuilder: (context, index) {
                            return _buildBesoinCard(_besoinsFiltered[index]);
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  /// Widget pour état vide
  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.inbox_outlined,
            size: 80,
            color: Colors.grey[400],
          ),
          const SizedBox(height: 16),
          Text(
            _selectedTypeId != null
                ? 'Aucun besoin pour ce type'
                : 'Aucun besoin trouvé',
            style: TextStyle(
              fontSize: 18,
              color: Colors.grey[600],
            ),
          ),
          if (_selectedTypeId != null)
            Padding(
              padding: const EdgeInsets.only(top: 16),
              child: TextButton.icon(
                onPressed: () => _applyFilter(null),
                icon: const Icon(Icons.clear),
                label: const Text('Effacer le filtre'),
              ),
            ),
        ],
      ),
    );
  }

  /// Card pour un besoin
  Widget _buildBesoinCard(Besoin besoin) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header: Type + Badge urgent
            Row(
              children: [
                // Type de don
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: Theme.of(context).primaryColor.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        _getIconForType(besoin.typeName),
                        size: 16,
                        color: Theme.of(context).primaryColor,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        besoin.typeName,
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: Theme.of(context).primaryColor,
                        ),
                      ),
                    ],
                  ),
                ),
                
                const SizedBox(width: 8),
                
                // Badge urgent
                if (besoin.isUrgent)
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.red,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          Icons.warning,
                          size: 14,
                          color: Colors.white,
                        ),
                        SizedBox(width: 4),
                        Text(
                          'URGENT',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                      ],
                    ),
                  ),
              ],
            ),
            
            const SizedBox(height: 12),
            
            // Titre
            Text(
              besoin.title,
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            
            const SizedBox(height: 8),
            
            // Description
            Text(
              besoin.description,
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey[700],
              ),
            ),
            
            const SizedBox(height: 12),
            
            // Infos supplémentaires
            Wrap(
              spacing: 16,
              runSpacing: 8,
              children: [
                // Quantité
                if (besoin.quantity != null && besoin.quantity!.isNotEmpty)
                  _buildInfoChip(
                    icon: Icons.inventory,
                    label: besoin.quantity!,
                  ),
                
                // Localisation
                if (besoin.location != null && besoin.location!.isNotEmpty)
                  _buildInfoChip(
                    icon: Icons.location_on,
                    label: besoin.location!,
                  ),
                
                // Date limite
                if (besoin.deadline != null && besoin.deadline!.isNotEmpty)
                  _buildInfoChip(
                    icon: Icons.calendar_today,
                    label: _formatDate(besoin.deadline!),
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  /// Chip info
  Widget _buildInfoChip({
    required IconData icon,
    required String label,
  }) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(
          icon,
          size: 16,
          color: Colors.grey[600],
        ),
        const SizedBox(width: 4),
        Text(
          label,
          style: TextStyle(
            fontSize: 13,
            color: Colors.grey[700],
          ),
        ),
      ],
    );
  }

  /// Obtenir icône selon type de don
  IconData _getIconForType(String typeName) {
    switch (typeName.toLowerCase()) {
      case 'vêtements':
      case 'vetements':
        return Icons.checkroom;
      case 'nourriture':
        return Icons.restaurant;
      case 'médicaments':
      case 'medicaments':
        return Icons.medical_services;
      case 'argent':
        return Icons.attach_money;
      case 'jouets':
        return Icons.toys;
      case 'livres':
        return Icons.book;
      default:
        return Icons.volunteer_activism;
    }
  }

  /// Formater date
  String _formatDate(String date) {
    try {
      final dt = DateTime.parse(date);
      return '${dt.day}/${dt.month}/${dt.year}';
    } catch (e) {
      return date;
    }
  }
}

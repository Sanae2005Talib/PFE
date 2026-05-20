import 'package:flutter/material.dart';
import '../models/association.dart';
import '../services/api_service.dart';
import 'besoins_list.dart';

/// Page de détails d'une association
/// Affiche les informations complètes + stats + bouton vers besoins
class AssociationDetailScreen extends StatefulWidget {
  final Association association;

  const AssociationDetailScreen({
    super.key,
    required this.association,
  });

  @override
  State<AssociationDetailScreen> createState() => _AssociationDetailScreenState();
}

class _AssociationDetailScreenState extends State<AssociationDetailScreen> {
  final ApiService _apiService = ApiService();
  
  // Données détaillées
  Map<String, dynamic>? _details;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadDetails();
  }

  /// Charger les détails depuis l'API
  Future<void> _loadDetails() async {
    setState(() => _loading = true);
    
    final details = await _apiService.getAssociationDetails(widget.association.id);
    
    setState(() {
      _details = details;
      _loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Détails Association'),
      ),
      
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _details == null
              ? _buildErrorState()
              : RefreshIndicator(
                  onRefresh: _loadDetails,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Header avec logo/icône
                        _buildHeader(),
                        
                        // Informations principales
                        _buildInfoSection(),
                        
                        // Statistiques
                        _buildStatsSection(),
                        
                        // Bouton voir besoins
                        _buildActionButton(),
                        
                        const SizedBox(height: 24),
                      ],
                    ),
                  ),
                ),
    );
  }

  /// Header avec logo
  Widget _buildHeader() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            Theme.of(context).primaryColor,
            Theme.of(context).colorScheme.secondary,
          ],
        ),
      ),
      child: Column(
        children: [
          // Logo / Icône
          Container(
            width: 100,
            height: 100,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.1),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Icon(
              Icons.business,
              size: 50,
              color: Theme.of(context).primaryColor,
            ),
          ),
          
          const SizedBox(height: 16),
          
          // Nom association
          Text(
            widget.association.associationName,
            style: const TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
            textAlign: TextAlign.center,
          ),
          
          const SizedBox(height: 8),
          
          // Région
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(
                Icons.location_on,
                size: 18,
                color: Colors.white,
              ),
              const SizedBox(width: 4),
              Text(
                widget.association.regionName ?? 'Non spécifié',
                style: const TextStyle(
                  fontSize: 16,
                  color: Colors.white,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  /// Section informations
  Widget _buildInfoSection() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Titre section
          const Text(
            'Informations',
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
          
          const SizedBox(height: 16),
          
          // Description
          if (widget.association.description != null &&
              widget.association.description!.isNotEmpty)
            _buildInfoCard(
              icon: Icons.description,
              title: 'Description',
              content: widget.association.description!,
            ),
          
          const SizedBox(height: 12),
          
          // Téléphone
          _buildInfoCard(
            icon: Icons.phone,
            title: 'Téléphone',
            content: widget.association.phone,
          ),
          
          const SizedBox(height: 12),
          
          // Adresse
          _buildInfoCard(
            icon: Icons.location_city,
            title: 'Adresse',
            content: widget.association.address,
          ),
        ],
      ),
    );
  }

  /// Card info
  Widget _buildInfoCard({
    required IconData icon,
    required String title,
    required String content,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.grey[100],
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(
            icon,
            color: Theme.of(context).primaryColor,
            size: 24,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey[600],
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  content,
                  style: const TextStyle(
                    fontSize: 16,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  /// Section statistiques
  Widget _buildStatsSection() {
    if (_details == null || _details!['stats'] == null) {
      return const SizedBox.shrink();
    }

    final stats = _details!['stats'];
    
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Statistiques',
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
          
          const SizedBox(height: 16),
          
          // Cards stats
          Row(
            children: [
              // Total besoins
              Expanded(
                child: _buildStatCard(
                  label: 'Total',
                  value: stats['total_needs']?.toString() ?? '0',
                  icon: Icons.list_alt,
                  color: Colors.blue,
                ),
              ),
              
              const SizedBox(width: 12),
              
              // Besoins urgents
              Expanded(
                child: _buildStatCard(
                  label: 'Urgents',
                  value: stats['urgent_needs']?.toString() ?? '0',
                  icon: Icons.warning,
                  color: Colors.orange,
                ),
              ),
              
              const SizedBox(width: 12),
              
              // Besoins satisfaits
              Expanded(
                child: _buildStatCard(
                  label: 'Satisfaits',
                  value: stats['satisfied_needs']?.toString() ?? '0',
                  icon: Icons.check_circle,
                  color: Colors.green,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  /// Card stat
  Widget _buildStatCard({
    required String label,
    required String value,
    required IconData icon,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: color.withOpacity(0.3),
          width: 1,
        ),
      ),
      child: Column(
        children: [
          Icon(
            icon,
            color: color,
            size: 32,
          ),
          const SizedBox(height: 8),
          Text(
            value,
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: TextStyle(
              fontSize: 12,
              color: Colors.grey[700],
            ),
          ),
        ],
      ),
    );
  }

  /// Bouton action - Voir besoins
  Widget _buildActionButton() {
    final totalNeeds = _details?['stats']?['total_needs'] ?? 0;
    
    return Padding(
      padding: const EdgeInsets.all(16),
      child: SizedBox(
        width: double.infinity,
        child: ElevatedButton.icon(
          onPressed: totalNeeds > 0
              ? () {
                  // Navigation vers liste des besoins
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => BesoinsListScreen(
                        association: widget.association,
                      ),
                    ),
                  );
                }
              : null,
          icon: const Icon(Icons.volunteer_activism, size: 24),
          label: Text(
            totalNeeds > 0
                ? 'Voir les besoins ($totalNeeds)'
                : 'Aucun besoin actif',
            style: const TextStyle(fontSize: 18),
          ),
          style: ElevatedButton.styleFrom(
            padding: const EdgeInsets.symmetric(vertical: 16),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
          ),
        ),
      ),
    );
  }

  /// État erreur
  Widget _buildErrorState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.error_outline,
            size: 80,
            color: Colors.grey[400],
          ),
          const SizedBox(height: 16),
          Text(
            'Erreur de chargement',
            style: TextStyle(
              fontSize: 18,
              color: Colors.grey[600],
            ),
          ),
          const SizedBox(height: 16),
          ElevatedButton.icon(
            onPressed: _loadDetails,
            icon: const Icon(Icons.refresh),
            label: const Text('Réessayer'),
          ),
        ],
      ),
    );
  }
}

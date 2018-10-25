using System.Collections;
using UnityEngine.UI;
using UnityEngine;

using UnityEngine.UI;
public class prosessoal : MonoBehaviour {
	[SerializeField] Text tekspertanyaan;

	[SerializeField] Text[] tekspilihan;
	[SerializeField] soall[] soal;
	[SerializeField] int indeksSoal;
	int a,b,m,x,hasil;
	public Text hi;
	public GameObject showpanelnotif;
	public GameObject panelbenar;
	public GameObject panelsalah;
	public GameObject close;
	public GameObject[] hidejwb;
	public Button[] aktif;
	public int ran;
	public static int hasill;


	void Start(){

		Tampilsoal (Random.Range(0,soal.Length));

	}
	IEnumerator waktucom(){
		yield return new WaitForSeconds (3);
		jawabcom ();

		}
	void Update(){
		
	}
	public void Tampilsoal(int _indeksSoal){
		indeksSoal = _indeksSoal;
		tekspertanyaan.text = soal [_indeksSoal].pertanyaan;
		for(int i = 0; i< soal[_indeksSoal].pilihan.Length;i++){
			tekspilihan[i].text = soal[_indeksSoal].pilihan[i];
		
		}
	}
	public void s(){
		
	}
	IEnumerator delaynotifbenar(){
		yield return new WaitForSeconds (2);

		panelbenar.SetActive (false);
		close.SetActive (false);
	}

	IEnumerator delaynotifsalah(){
		yield return new WaitForSeconds (2);

		panelsalah.SetActive (false);
		close.SetActive (false);
	}

	public void benar(){
		
	
		uangplayerbaru.uangplayer += 500;
	
		panelbenar.SetActive (true);
		hidejwb [0].SetActive (false);
		hidejwb [1].SetActive (false);
		StartCoroutine (delaynotifbenar ());
		Debug.Log ("hasil " + hasill);

	}
	public void salah(){
		uangplayerbaru.uangplayer -=200 ;
		panelsalah.SetActive (true);
		hidejwb [0].SetActive (false);
		hidejwb [1].SetActive (false);
		StartCoroutine (delaynotifsalah ());
		Debug.Log ("hasil " + hasill);
	
	}
	public void benarcomp(){


		uangcomputerbaru.uangcomputer += 500;

		panelbenar.SetActive (true);
		hidejwb [0].SetActive (false);
		hidejwb [1].SetActive (false);
		StartCoroutine (delaynotifbenar ());
		Debug.Log ("hasil " + hasill);

	}
	public void salahcomp(){
		uangcomputerbaru.uangcomputer += 200;
		panelsalah.SetActive (true);
		hidejwb [0].SetActive (false);
		hidejwb [1].SetActive (false);
		StartCoroutine (delaynotifsalah ());
		Debug.Log ("hasil " + hasill);

	}
	public void jawabcom ()
	{
		

		ran = Random.Range (2, 5);
		Debug.Log ("as " + ran);
		if (ran == 2 && ran == 3) {
			hasill = 0;
			if(ran == 4 && ran == 5)
				hasill = 1;
			
		} 
	

		if (hasill == soal [indeksSoal].indeksJawaban) 
			benarcomp ();
			else

				salahcomp();


	}

	public void verifikasijawaban (int _indeksJawaban)
	{
		
		if (_indeksJawaban == soal [indeksSoal].indeksJawaban)
			benar ();
		else
			salah ();	
	
	
	}
}

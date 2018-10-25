using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.UI;
public class oi : MonoBehaviour {
	public GameObject rumahkartu1;
	public GameObject rumahkartu2;
	public GameObject rumahkartu3;
	public GameObject kpulaujawa;
	public GameObject rumahkartu5;
	public GameObject rumahkartu6;
	public GameObject rumahkartu8;
	public GameObject rumahkartu9;
	public GameObject rumahkartu10;
	public GameObject kpulausumatra;
	public GameObject rumahkartu12;
	public GameObject rumahkartu13;
	public GameObject rumahkartu16;
	public GameObject rumahkartu17;
	public GameObject rumahkartu18;
	public GameObject kpulausulewesi;
	public GameObject rumahkartu20;
	public GameObject rumahkartu21;
	public GameObject rumahkartu22;
	public GameObject rumahkartu24;
	public GameObject kpulaukalimantan;
	public GameObject rumahkartu27;
	public GameObject rumahkartu28;
	public GameObject soal1;
	public GameObject soal2;
	public GameObject soal3;
	public GameObject soal4;
	public AIcomputer cek;
	public GameObject[] close;
	public GameObject penjara;
	public PLAYER namavar;
	public double kk;
	private AudioSource penjarasound;
	public AudioClip soundpenjara;
	private AudioSource showpanelsuara;
	public AudioClip showsuarapanel;
	public Text uangpemain;
	void Awake () {

		penjarasound = GetComponent<AudioSource>();
		showpanelsuara = GetComponent<AudioSource>();
	}
	// Use this for initialization
	private Rigidbody rb;

	int hitName;

	// Use this for initialization
	void Start(){
		rb = GetComponent<Rigidbody> ();
	}

	public void closekonten(){
		close [0].SetActive (false);
		close [1].SetActive (false);
		close [2].SetActive (false);
		close [3].SetActive (false);
		close [4].SetActive (false);
		close [5].SetActive (false);
		close [6].SetActive (false);
		close [7].SetActive (false);
		close [8].SetActive (false);
		close [9].SetActive (false);
		close [10].SetActive (false);
		close [11].SetActive (false);
		close [12].SetActive (false);
		close [13].SetActive (false);
		close [14].SetActive (false);
		close [15].SetActive (false);
		close [16].SetActive (false);
		close [17].SetActive (false);
		close [18].SetActive (false);
		close [19].SetActive (false);
		close [20].SetActive (false);
		close [21].SetActive (false);
		close [22].SetActive (false);
		close [23].SetActive (false);
	}

	IEnumerator waktu_rumahk1(){
		
		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu1.SetActive (true);
	}
	IEnumerator waktu_rumahk2(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu2.SetActive (true);

	}	
	IEnumerator waktu_rumahk3(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu3.SetActive (true);

	}
	IEnumerator waktu_rumahk5(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu5.SetActive (true);

	}
	IEnumerator waktu_rumahk6(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu6.SetActive (true);

	}
	IEnumerator waktu_rumahk8(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu8.SetActive (true);

	}
	IEnumerator waktu_rumahk9(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu9.SetActive (true);

	}
	IEnumerator waktu_rumahk10(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu10.SetActive (true);

	}
	IEnumerator waktu_rumahk12(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu12.SetActive (true);

	}
	IEnumerator waktu_rumahk13(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu13.SetActive (true);

	}
	IEnumerator waktu_rumahk16(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu16.SetActive (true);

	}
	IEnumerator waktu_rumahk17(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu17.SetActive (true);

	}
	IEnumerator waktu_rumahk18(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu18.SetActive (true);

	}
	IEnumerator waktu_rumahk20(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu20.SetActive (true);

	}
	IEnumerator waktu_rumahk21(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu21.SetActive (true);

	}
	IEnumerator waktu_rumahk22(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu22.SetActive (true);

	}
	IEnumerator waktu_rumahk24(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu24.SetActive (true);

	}
	IEnumerator waktu_rumahk27(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu27.SetActive (true);

	}
	IEnumerator waktu_rumahk28(){

		yield return new WaitForSeconds (2);
		showpanelsuara.PlayOneShot (showsuarapanel);
		rumahkartu28.SetActive (true);

	}
	IEnumerator waktu_pulaujawa(){

		yield return new WaitForSeconds (2);
		kpulaujawa.SetActive (true);
		showpanelsuara.PlayOneShot (showsuarapanel);
	}

	IEnumerator waktu_pulausulewesi(){

		yield return new WaitForSeconds (2);
		kpulausulewesi.SetActive (true);
	}

	IEnumerator waktu_pulausumatra(){

		yield return new WaitForSeconds (2);
		kpulausumatra.SetActive (true);
	}
	IEnumerator waktu_pulaukalimantan(){

		yield return new WaitForSeconds (2);
		kpulaukalimantan.SetActive (true);
	}
	IEnumerator waktu_penjara(){

		yield return new WaitForSeconds (2);
		penjara.SetActive (true);
	}
	public void OnTriggerEnter(Collider collider){
		
		if (namavar.Counterpos == namavar.targetpos) {
			

		
	
		
			if (collider.gameObject.name == "2") {
				
				StartCoroutine (waktu_rumahk2 ());

			}
			if (collider.gameObject.name == "3") {
				
				StartCoroutine (waktu_rumahk3 ());

			}	
			if (collider.gameObject.name == "pulaujawa") {
				StartCoroutine (waktu_pulaujawa ());
				showpanelsuara.PlayOneShot (showsuarapanel);

			}
			if (collider.gameObject.name == "5") {

				StartCoroutine (waktu_rumahk5 ());

			}
			if (collider.gameObject.name == "6") {

				StartCoroutine (waktu_rumahk6 ());

			}
			if (collider.gameObject.name == "soal1") {

				soal1.SetActive (true);

			}
			if (collider.gameObject.name == "penjara") {
				penjarasound.PlayOneShot (soundpenjara);
				StartCoroutine (waktu_penjara ());
			}
			if (collider.gameObject.name == "8") {

				StartCoroutine (waktu_rumahk8 ());

			}
			if (collider.gameObject.name == "9") {

				StartCoroutine (waktu_rumahk9 ());

			}
			if (collider.gameObject.name == "10") {

				StartCoroutine (waktu_rumahk10 ());

			}
			if (collider.gameObject.name == "pulausumatra") {

				StartCoroutine (waktu_pulausumatra ());

			}
			if (collider.gameObject.name == "12") {

				StartCoroutine (waktu_rumahk12 ());

			}
			if (collider.gameObject.name == "13") {

				StartCoroutine (waktu_rumahk13 ());

			}
			if (collider.gameObject.name == "soal2") {



			}
			if (collider.gameObject.name == "16") {

				StartCoroutine (waktu_rumahk16 ());

			}
			if (collider.gameObject.name == "17") {

				StartCoroutine (waktu_rumahk17 ());

			}
			if (collider.gameObject.name == "18") {

				StartCoroutine (waktu_rumahk18 ());

			}
			if (collider.gameObject.name == "pulausulewesi") {
				StartCoroutine (waktu_pulausulewesi ());
				showpanelsuara.PlayOneShot (showsuarapanel);

			}
			if (collider.gameObject.name == "20") {

				StartCoroutine (waktu_rumahk20 ());

			}
			if (collider.gameObject.name == "21") {
				StartCoroutine (waktu_rumahk21 ());
				showpanelsuara.PlayOneShot (showsuarapanel);

			}
			if (collider.gameObject.name == "22") {
				StartCoroutine (waktu_rumahk22 ());
				showpanelsuara.PlayOneShot (showsuarapanel);

			}
			if (collider.gameObject.name == "pajak") {

				showpanelsuara.PlayOneShot (showsuarapanel);

			}
			if (collider.gameObject.name == "24") {
				StartCoroutine (waktu_rumahk24 ());
				showpanelsuara.PlayOneShot (showsuarapanel);

			}
			if (collider.gameObject.name == "pulaukalimantan") {
				StartCoroutine (waktu_pulaukalimantan ());
				showpanelsuara.PlayOneShot (showsuarapanel);

			}

			if (collider.gameObject.name == "27") {
				StartCoroutine (waktu_rumahk27 ());
				showpanelsuara.PlayOneShot (showsuarapanel);

			}
			if (collider.gameObject.name == "28") {
				StartCoroutine (waktu_rumahk28 ());
				showpanelsuara.PlayOneShot (showsuarapanel);

			}
		}
	
	}

	public void OnTriggerExit(Collider col){
		rumahkartu1.SetActive (false);
		rumahkartu2.SetActive (false);
		rumahkartu3.SetActive (false);
		kpulaujawa.SetActive (false);
		rumahkartu5.SetActive (false);
		rumahkartu6.SetActive (false);
		rumahkartu8.SetActive (false);
		rumahkartu9.SetActive (false);
		rumahkartu10.SetActive (false);
		kpulausumatra.SetActive (false);
		rumahkartu12.SetActive (false);
		rumahkartu13.SetActive (false);
		soal2.SetActive (false);
		rumahkartu16.SetActive (false);
		rumahkartu17.SetActive (false);
		rumahkartu18.SetActive (false);
		rumahkartu20.SetActive (false);
		kpulausulewesi.SetActive (false);
		rumahkartu21.SetActive (false);
		rumahkartu22.SetActive (false);
		kpulaukalimantan.SetActive (false);
		rumahkartu24.SetActive (false);
		rumahkartu27.SetActive (false);
		rumahkartu28.SetActive (false);
		soal1.SetActive (false);
		penjara.SetActive (false);
		soal3.SetActive (false);
		soal4.SetActive (false);


	}

	}
		
	

